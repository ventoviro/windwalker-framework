<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Database;

use Psr\Log\LoggerInterface;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\Mysqli\MysqliDriver;
use Windwalker\Database\Driver\Pdo\PdoDriver;
use Windwalker\Database\Driver\Pgsql\PgsqlDriver;
use Windwalker\Database\Driver\Sqlsrv\SqlsrvDriver;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Platform\MySQLPlatform;
use Windwalker\Database\Platform\PostgreSQLPlatform;
use Windwalker\Database\Platform\SQLitePlatform;
use Windwalker\Database\Platform\SQLServerPlatform;
use Windwalker\Pool\ConnectionPool;
use Windwalker\Pool\PoolInterface;
use Windwalker\Pool\Stack\StackInterface;
use Windwalker\Query\Grammar\AbstractGrammar;

/**
 * The DatabaseFactory class.
 */
class DatabaseFactory implements DatabaseFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function create(
        string $driverName,
        array $options,
        ?PoolInterface $pool = null,
        ?LoggerInterface $logger = null,
    ): DatabaseAdapter {
        [, $platformShortName] = static::extractDriverName($driverName);

        $options['driver'] = $driverName;

        return new DatabaseAdapter(
            $this->createDriver(
                $driverName,
                $options,
                $pool ?? $this->createConnectionPool($options['pool'] ?? [])
            ),
            $this->createPlatform($platformShortName),
            $logger
        );
    }

    /**
     * @inheritDoc
     */
    public function createDriver(
        string $driverName,
        array $options,
        ?PoolInterface $pool = null
    ): AbstractDriver {
        [$driverName, $platformName] = static::extractDriverName($driverName);

        $driverName = ucfirst(static::getDriverShortName($driverName));

        $driverClass = match ($driverName) {
            'pdo' => PdoDriver::class,
            'pgsql' => PgsqlDriver::class,
            'sqlsrv' => SqlsrvDriver::class,
            'mysqli' => MysqliDriver::class,
            default => sprintf(
                __NAMESPACE__ . '\Driver\%s\%sDriver',
                $driverName,
                $driverName
            )
        };

        $options['platform'] = $platformName;

        return new $driverClass(
            $options,
            $pool
        );
    }

    /**
     * @inheritDoc
     */
    public function createPlatform(string $platform, ?AbstractGrammar $grammar = null): AbstractPlatform
    {
        $platformName = static::getPlatformName($platform);

        $class = match ($platformName) {
            AbstractPlatform::MYSQL => MySQLPlatform::class,
            AbstractPlatform::POSTGRESQL => PostgreSQLPlatform::class,
            AbstractPlatform::SQLSERVER => SQLServerPlatform::class,
            AbstractPlatform::SQLITE => SQLitePlatform::class,
            default => __NAMESPACE__ . '\\' . $platformName . 'Platform',
        };

        return new $class($grammar);
    }

    /**
     * extractDriverName
     *
     * @param  string  $name
     *
     * @return  string[]
     */
    public static function extractDriverName(string $name): array
    {
        $names = explode('_', $name, 2);

        if (\Windwalker\count($names) === 1) {
            return [$names[0], $names[0]];
        }

        return $names;
    }

    public static function getDriverShortName(string $platform): string
    {
        return strtolower(
            match (strtolower($platform)) {
                'postgresql' => 'pgsql',
                'sqlserver' => 'sqlsrv',
                default => $platform
            }
        );
    }

    public static function getPlatformName(string $platform): string
    {
        return match (strtolower($platform)) {
            'pgsql', 'postgresql' => 'PostgreSQL',
            'sqlsrv', 'sqlserver' => 'SQLServer',
            'mysql', 'mysqli' => 'MySQL',
            'sqlite' => 'SQLite',
            default => ucfirst($platform),
        };
    }

    /**
     * @inheritDoc
     */
    public function createGrammar(?string $platform = null): AbstractGrammar
    {
        $platform = static::getPlatformName($platform);

        return AbstractGrammar::create($platform);
    }

    /**
     * @inheritDoc
     */
    public function createConnectionPool(
        array $options = [],
        ?StackInterface $stack = null,
        ?LoggerInterface $logger = null
    ): ConnectionPool {
        return new ConnectionPool(
            $options,
            $stack,
            $logger
        );
    }
}
