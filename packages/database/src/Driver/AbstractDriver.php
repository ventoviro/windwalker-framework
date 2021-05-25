<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver;

use JetBrains\PhpStorm\Pure;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Event\QueryEndEvent;
use Windwalker\Database\Event\QueryFailedEvent;
use Windwalker\Database\Exception\DatabaseQueryException;
use Windwalker\Database\Hydrator\HydratorAwareInterface;
use Windwalker\Database\Hydrator\HydratorInterface;
use Windwalker\Database\Hydrator\SimpleHydrator;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Schema\AbstractSchemaManager;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Pool\ConnectionPool;
use Windwalker\Pool\PoolInterface;
use Windwalker\Query\Query;
use Windwalker\Utilities\Options\OptionsResolverTrait;

/**
 * The AbstractDriver class.
 */
abstract class AbstractDriver implements HydratorAwareInterface
{
    use OptionsResolverTrait;

    /**
     * @var string
     */
    protected static string $name = '';

    /**
     * @var string
     */
    protected string $platformName = '';

    /**
     * @var Query|string
     */
    protected mixed $lastQuery;

    /**
     * @var ?AbstractSchemaManager
     */
    protected ?AbstractSchemaManager $schema = null;

    protected ?PoolInterface $pool = null;

    protected ?HydratorInterface $hydrator = null;

    /**
     * AbstractPlatform constructor.
     *
     * @param  array               $options
     * @param  PoolInterface|null  $pool
     */
    public function __construct(array $options, ?PoolInterface $pool = null)
    {
        $this->resolveOptions(
            $options,
            [$this, 'configureOptions']
        );

        $this->setPool($pool);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'driver' => null,
                'host' => 'localhost',
                'dbname' => null,
                'user' => null,
                'password' => null,
                'port' => null,
                'prefix' => null,
                'charset' => null,
                'platform' => null,
                'driverOptions' => [],
            ]
        )
            ->setRequired(
                [
                    'driver',
                    'host',
                    'user',
                ]
            );
        // ->setAllowedTypes('driver', 'string');
    }

    protected function handleQuery($query, ?array &$bounded = [], $emulated = false): string
    {
        $this->lastQuery = $query;

        if ($query instanceof Query) {
            return $this->replacePrefix($query->render($emulated, $bounded));
        }

        $bounded = $bounded ?? [];

        return $this->replacePrefix((string) $query);
    }

    /**
     * Get a connection, must release manually.
     *
     * @return  ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        $conn = $this->getConnectionFromPool();

        if ($conn->isConnected()) {
            return $conn;
        }

        $conn->connect();

        return $conn;
    }

    /**
     * @inheritDoc
     */
    public function useConnection(callable $callback): mixed
    {
        $conn = $this->getConnection();

        try {
            $result = $callback($conn);
        } finally {
            $conn->release();
        }

        return $result;
    }

    /**
     * disconnect
     *
     * @return  int
     */
    public function disconnectAll(): int
    {
        return $this->getPool()->close();
    }

    /**
     * createStatement
     *
     * @param  string  $query
     * @param  array   $bounded
     * @param  array   $options
     *
     * @return  StatementInterface
     */
    abstract protected function createStatement(
        string $query,
        array $bounded = [],
        array $options = []
    ): StatementInterface;

    /**
     * @inheritDoc
     */
    public function prepare(mixed $query, array $options = []): StatementInterface
    {
        // Convert query to string and get merged bounded
        $sql = $this->handleQuery($query, $bounded);

        // Prepare actions by driver
        $stmt = $this->createStatement($sql, $bounded, $options);

        if ($query instanceof EventAwareInterface) {
            $stmt->addDispatcherDealer($query->getEventDispatcher());
        } elseif ($query instanceof EventDispatcherInterface) {
            $stmt->addDispatcherDealer($query);
        }

        // Register monitor events
        $stmt->on(
            QueryFailedEvent::class,
            function (QueryFailedEvent $event) use (
                $query,
                $sql,
                $bounded
            ) {
                $event->setQuery($query)
                    ->setSql($this->handleQuery($query, $bounded, true))
                    ->setBounded($bounded);

                $e = $event->getException();

                $sql = $this->replacePrefix(($query instanceof Query ? $query->render(true) : (string) $query));

                $event->setException(
                    new DatabaseQueryException(
                        $e->getMessage() . ' - SQL: ' . $sql,
                        (int) $e->getCode(),
                        $e
                    )
                );
            }
        );

        $stmt->on(
            QueryEndEvent::class,
            function (QueryEndEvent $event) use (
                $query,
                $bounded
            ) {
                $event->setQuery($query)
                    ->setSql($this->handleQuery($query, $bounded, true))
                    ->setBounded($bounded);
            }
        );

        return $stmt;
    }

    /**
     * @inheritDoc
     */
    public function execute(mixed $query, ?array $params = null): StatementInterface
    {
        return $this->prepare($query)->execute($params);
    }

    abstract public function getVersion(): string;

    /**
     * Replace the table prefix.
     *
     * @see     https://stackoverflow.com/a/31745275
     *
     * @param  string  $sql     The SQL statement to prepare.
     * @param  string  $prefix  The common table prefix.
     *
     * @return  string  The processed SQL statement.
     */
    public function replacePrefix(string $sql, string $prefix = '#__'): string
    {
        if ($prefix === '' || !str_contains($sql, $prefix)) {
            return $sql;
        }

        $array = [];

        if ($number = preg_match_all('#((?<![\\\])[\'"])((?:.(?!(?<![\\\])\1))*.?)\1#i', $sql, $matches)) {
            for ($i = 0; $i < $number; $i++) {
                if (!empty($matches[0][$i])) {
                    $array[$i] = trim($matches[0][$i]);
                    $sql       = str_replace($matches[0][$i], '<#encode:' . $i . ':code#>', $sql);
                }
            }
        }

        $sql = str_replace($prefix, $this->getOption('prefix'), $sql);

        foreach ($array as $key => $js) {
            $sql = str_replace('<#encode:' . $key . ':code#>', $js, $sql);
        }

        return $sql;
    }

    /**
     * @return string
     */
    public function getPlatformName(): string
    {
        return $this->platformName;
    }

    /**
     * @param  string  $platformName
     *
     * @return  static  Return self to support chaining.
     */
    public function setPlatformName(string $platformName): static
    {
        $this->platformName = $platformName;

        return $this;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnectionFromPool(): ConnectionInterface
    {
        /** @var ConnectionInterface $connection */
        $connection = $this->getPool()->getConnection();

        return $connection;
    }

    public function createConnection(): ConnectionInterface
    {
        $class = $this->getConnectionClass();

        return new $class($this->getOptions());
    }

    #[Pure]
    protected function getConnectionClass(): string
    {
        $class = __NAMESPACE__ . '\%s\%sConnection';

        return sprintf(
            $class,
            ucfirst(static::$name),
            ucfirst(static::$name)
        );
    }

    public function isSupported(): bool
    {
        return $this->getConnectionClass()::isSupported();
    }

    public function __destruct()
    {
        $this->disconnectAll();
    }

    /**
     * @param  PoolInterface|null  $pool
     *
     * @return  static  Return self to support chaining.
     */
    public function setPool(?PoolInterface $pool): static
    {
        $this->pool = $this->preparePool($pool);

        return $this;
    }

    protected function preparePool(?PoolInterface $pool): ConnectionPool
    {
        if (!$pool) {
            $options = $this->getOptions();

            $pool = (new DatabaseFactory())
                ->createConnectionPool($options['pool'] ?? []);
        }

        $pool->setConnectionBuilder(
            function () {
                return $this->createConnection();
            }
        );
        $pool->init();

        return $pool;
    }

    /**
     * @return PoolInterface
     */
    public function getPool(): PoolInterface
    {
        return $this->pool ??= $this->preparePool(null);
    }

    public function getHydrator(): HydratorInterface
    {
        return $this->hydrator ??= new SimpleHydrator();
    }

    /**
     * @param  HydratorInterface|null  $hydrator
     *
     * @return  static  Return self to support chaining.
     */
    public function setHydrator(?HydratorInterface $hydrator): static
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    /**
     * Quote and escape a value.
     *
     * @param  string  $value
     *
     * @return  string
     */
    abstract public function quote(string $value): string;

    /**
     * Escape a value.
     *
     * @param  string  $value
     *
     * @return  string
     */
    abstract public function escape(string $value): string;
}
