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
use Windwalker\Pool\ConnectionPool;
use Windwalker\Pool\PoolInterface;
use Windwalker\Pool\Stack\StackInterface;
use Windwalker\Query\Grammar\AbstractGrammar;

/**
 * Interface DatabaseFactoryInterface
 */
interface DatabaseFactoryInterface
{
    /**
     * createByDriverName
     *
     * @param  string                $driverName
     * @param  array                 $options
     * @param  PoolInterface|null    $pool
     * @param  LoggerInterface|null  $logger
     *
     * @return  DatabaseAdapter
     */
    public function create(
        string $driverName,
        array $options,
        ?PoolInterface $pool = null,
        ?LoggerInterface $logger = null,
    ): DatabaseAdapter;

    /**
     * createPlatform
     *
     * @param  string                $platform
     * @param  AbstractGrammar|null  $grammar
     *
     * @return  AbstractPlatform
     */
    public function createPlatform(string $platform, ?AbstractGrammar $grammar = null): AbstractPlatform;

    /**
     * createDriver
     *
     * @param  string              $driverName
     * @param  array               $options
     * @param  PoolInterface|null  $pool
     *
     * @return  AbstractDriver
     */
    public function createDriver(
        string $driverName,
        array $options,
        ?PoolInterface $pool = null
    ): AbstractDriver;

    /**
     * Create Grammar object.
     *
     * @param  string|null  $platform
     *
     * @return  AbstractGrammar
     */
    public function createGrammar(?string $platform = null): AbstractGrammar;

    /**
     * createConnectionPool
     *
     * @param  array                 $options
     * @param  StackInterface|null   $stack
     * @param  LoggerInterface|null  $logger
     *
     * @return  ConnectionPool
     */
    public function createConnectionPool(
        array $options = [],
        ?StackInterface $stack = null,
        ?LoggerInterface $logger = null
    ): ConnectionPool;
}
