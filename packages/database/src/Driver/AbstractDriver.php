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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\DatabaseFactoryInterface;
use Windwalker\Database\Event\QueryEndEvent;
use Windwalker\Database\Event\QueryFailedEvent;
use Windwalker\Database\Exception\DatabaseQueryException;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Schema\AbstractSchemaManager;
use Windwalker\Pool\AbstractPool;
use Windwalker\Pool\ConnectionPool;
use Windwalker\Pool\PoolInterface;
use Windwalker\Query\Query;
use Windwalker\Utilities\Options\OptionsResolverTrait;

/**
 * The AbstractDriver class.
 */
abstract class AbstractDriver
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
     * @var ?AbstractPlatform
     */
    protected ?AbstractPlatform $platform = null;

    /**
     * @var ?AbstractSchemaManager
     */
    protected ?AbstractSchemaManager $schema = null;

    protected ?PoolInterface $pool = null;

    /**
     * AbstractPlatform constructor.
     *
     * @param  array               $options
     * @param  AbstractPlatform    $platform
     * @param  PoolInterface|null  $pool
     */
    public function __construct(array $options, AbstractPlatform $platform, ?PoolInterface $pool = null)
    {
        $this->resolveOptions(
            $options,
            [$this, 'configureOptions']
        );

        if ($this->options['driver'] === 'mysql') {
            $this->options['driver'] = 'pdo_mysql';
        }

        $this->platform = $platform;

        $this->setPlatformName($platform->getName());

        $this->setPool($pool);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'driver' => null,
                'host' => 'localhost',
                'database' => null,
                'username' => null,
                'password' => null,
                'port' => null,
                'prefix' => null,
                'charset' => null,
                'driverOptions' => [],
            ]
        )
            ->setRequired(
                [
                    'driver',
                    'host',
                    'username'
                ]
            );
            // ->setAllowedTypes('driver', 'string');
    }

    protected function handleQuery($query, ?array &$bounded = [], $emulated = false): string
    {
        $this->lastQuery = $query;

        if ($query instanceof Query) {
            return $query->render($emulated, $bounded);
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

        $result = $callback($conn);

        $conn->release();

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
     * @param  string  $sql  The SQL statement to prepare.
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

        $sql = str_replace($prefix, $this->db->getOption('prefix'), $sql);

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

    public function getPlatform(): AbstractPlatform
    {
        return $this->platform ??= $this->factory->createPlatform($this->getPlatformName());
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
        return $this->pool ??= $this->preparePool();
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
