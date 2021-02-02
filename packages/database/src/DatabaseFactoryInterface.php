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
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Pool\PoolInterface;

/**
 * Interface DatabaseFactoryInterface
 */
interface DatabaseFactoryInterface
{
    /**
     * createAdapter
     *
     * @param  AbstractDriver|null   $driver
     * @param  LoggerInterface|null  $logger
     *
     * @return  DatabaseAdapter
     */
    public function create(
        ?AbstractDriver $driver = null,
        ?LoggerInterface $logger = null,
    ): DatabaseAdapter;

    /**
     * createByDriverName
     *
     * @param  string                $driverName
     * @param  array                 $options
     * @param  LoggerInterface|null  $logger
     *
     * @return  DatabaseAdapter
     */
    public function createByDriverName(
        string $driverName,
        array $options,
        ?LoggerInterface $logger = null,
    ): DatabaseAdapter;

    /**
     * createPlatform
     *
     * @param  string           $platform
     * @param  DatabaseAdapter  $db
     *
     * @return  AbstractPlatform
     */
    public function createPlatform(string $platform, DatabaseAdapter $db): AbstractPlatform;

    /**
     * createDriver
     *
     * @param  string                 $driverName
     * @param  DatabaseAdapter        $db
     * @param  AbstractPlatform|null  $platform
     * @param  PoolInterface|null     $pool
     *
     * @return  AbstractDriver
     */
    public function createDriver(string $driverName,
        DatabaseAdapter $db,
        AbstractPlatform $platform = null,
        ?PoolInterface $pool = null
    ): AbstractDriver;
}
