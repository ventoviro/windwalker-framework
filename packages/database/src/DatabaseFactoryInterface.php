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
use Windwalker\Database\Driver\DriverInterface;
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
     * @param  array                 $options
     * @param  LoggerInterface|null  $logger
     *
     * @return  DatabaseAdapter
     */
    public function create(array $options = [], ?LoggerInterface $logger = null): DatabaseAdapter;

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
     * @param  string              $driverName
     * @param  DatabaseAdapter     $db
     * @param  PoolInterface|null  $pool
     *
     * @return  DriverInterface
     */
    public function createDriver(string $driverName, DatabaseAdapter $db, ?PoolInterface $pool = null): DriverInterface;
}
