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
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Event\QueryEndEvent;
use Windwalker\Database\Event\QueryFailedEvent;
use Windwalker\Database\Exception\DatabaseQueryException;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Schema\AbstractSchemaManager;
use Windwalker\Pool\AbstractPool;
use Windwalker\Pool\ConnectionPool;
use Windwalker\Query\Query;

/**
 * The AbstractDriver class.
 */
abstract class AbstractDriver implements DriverInterface
{
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
     * @var AbstractPlatform
     */
    protected ?AbstractPlatform $platform = null;

    /**
     * @var AbstractSchemaManager
     */
    protected ?AbstractSchemaManager $schema = null;

    /**
     * @var DatabaseAdapter
     */
    protected ?DatabaseAdapter $db = null;

    /**
     * @var ConnectionInterface
     */
    protected ?ConnectionInterface $connection = null;

    protected ?AbstractPool $pool = null;

    /**
     * AbstractPlatform constructor.
     *
     * @param  DatabaseAdapter  $db
     */
    public function __construct(DatabaseAdapter $db)
    {
        $this->db = $db;

        $this->preparePool();
    }

    /**
     * @return DatabaseAdapter
     */
    public function getDb(): DatabaseAdapter
    {
        return $this->db;
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
     * connect
     *
     * @return  ConnectionInterface
     */
    public function connect(): ConnectionInterface
    {
        $conn = $this->getConnection();

        if ($conn->isConnected()) {
            return $conn;
        }

        $conn->connect();

        return $conn;
    }

    /**
     * disconnect
     *
     * @return  mixed
     */
    public function disconnect(): mixed
    {
        return $this->getConnection()->disconnect();
    }

    abstract protected function doPrepare(string $query, array $bounded = [], array $options = []): StatementInterface;

    /**
     * @inheritDoc
     */
    public function prepare(mixed $query, array $options = []): StatementInterface
    {
        // Convert query to string and get merged bounded
        $sql = $this->handleQuery($query, $bounded);

        // Prepare actions by driver
        $stmt = $this->doPrepare($sql, $bounded, $options);

        // Make DatabaseAdapter listen to statement events
        $stmt->addDispatcherDealer($this->db->getDispatcher());

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
     * @param   string $sql    The SQL statement to prepare.
     * @param   string $prefix The common table prefix.
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
        if (!$this->platform) {
            $this->platform = AbstractPlatform::create($this->platformName, $this->db);
        }

        return $this->platform;
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
    public function getConnection(): ConnectionInterface
    {
        /** @var ConnectionInterface $connection */
        $connection = $this->pool->getConnection();

        return $connection;
    }

    public function createConnection(): ConnectionInterface
    {
        $class = $this->getConnectionClass();

        return new $class($this->db->getOptions());
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

    /**
     * @param  ConnectionInterface  $connection
     *
     * @return  static  Return self to support chaining.
     */
    public function setConnection(ConnectionInterface $connection): static
    {
        $this->connection = $connection;

        return $this;
    }

    public function isSupported(): bool
    {
        return $this->getConnectionClass()::isSupported();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @param  AbstractPool|null  $pool
     *
     * @return  static  Return self to support chaining.
     */
    public function setPool(?AbstractPool $pool): static
    {
        $this->pool = $pool;

        return $this;
    }

    protected function preparePool(): void
    {
        $options = $this->db->getOptions();

        $poolOptions = $options['pool'] ?? [];

        $this->pool = new ConnectionPool(
            $poolOptions,
            null,
            // todo: Add DB logger
            null
        );
        $this->pool->setConnectionBuilder(function () {
            return $this->createConnection();
        });
        $this->pool->init();
    }
}
