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
use Windwalker\Pool\PoolInterface;

/**
 * The DatabaseFactory class.
 */
class DatabaseFactory implements DatabaseFactoryInterface
{
    /**
     * @var array
     */
    protected array $platforms = [];

    /**
     * @var array
     */
    protected array $drivers = [];

    /**
     * @inheritDoc
     */
    public function create(
        ?AbstractDriver $driver = null,
        ?LoggerInterface $logger = null,
    ): DatabaseAdapter {
        return new DatabaseAdapter($driver, $logger);
    }

    /**
     * @inheritDoc
     */
    public function createByDriverName(
        string $driverName,
        array $options,
        ?LoggerInterface $logger = null,
    ): DatabaseAdapter {
        if (str_contains($driverName, '_')) {
            [$driverName, $platform] = explode('_', $driverName, 2);

            $this->createDriver($driverName)
        } else {
            $platform = static::getPlatformName($driverName);
        }
    }

    /**
     * @inheritDoc
     */
    public function createDriver(
        string $driverName,
        DatabaseAdapter $db,
        AbstractPlatform $platform = null,
        ?PoolInterface $pool = null
    ): AbstractDriver {
        $names = explode('_', $driverName);

        $platformName = ucfirst(static::getDriverShortName($names[0]));

        $driverClass = match ($platformName) {
            'pdo' => PdoDriver::class,
            'pgsql' => PgsqlDriver::class,
            'sqlsrv' => SqlsrvDriver::class,
            'mysqli' => MysqliDriver::class,
            default => sprintf(
                __NAMESPACE__ . '\%s\%sDriver',
                $platformName,
                $platformName
            )
        };

        $driver = new $driverClass($db);

        if (($driver instanceof PdoDriver) && isset($names[1])) {
            $driver->setPlatformName($names[1]);
        }

        return $driver;
    }

    /**
     * @inheritDoc
     */
    public function createPlatform(string $platform, DatabaseAdapter $db): AbstractPlatform
    {
        $platformName = static::getPlatformName($platform);

        $class = match ($platformName) {
            AbstractPlatform::MYSQL => MySQLPlatform::class,
            AbstractPlatform::POSTGRESQL => PostgreSQLPlatform::class,
            AbstractPlatform::SQLSERVER => SQLServerPlatform::class,
            AbstractPlatform::SQLITE => SQLitePlatform::class,
            default => __NAMESPACE__ . '\\' . $platformName . 'Platform',
        };

        return new $class($db);
    }

    public static function extractPlatformName($name): string
    {
        $names = explode('_', $name, 2);

        return $names[1] ?? $names[0];
    }

    public static function getDriverShortName(string $platform): string
    {
        return strtolower(
            match (strtolower($platform)) {
                'postgresql' => 'pgsql',
                'sqlserver' => 'sqlsrv',
            }
        );
    }

    public static function getPlatformName(string $platform): string
    {
        return match (strtolower($platform)) {
            'pgsql', 'postgresql' => 'PostgreSQL',
            'sqlsrv', 'sqlserver' => 'SQLServer',
            'mysql' => 'MySQL',
            'sqlite' => 'SQLite',
            default => ucfirst($platform),
        };
    }
}
