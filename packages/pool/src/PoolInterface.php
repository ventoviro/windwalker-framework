<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool;

/**
 * Interface PoolInterface
 */
interface PoolInterface
{
    /**
     * Initialize pool
     */
    public function init(): void;

    /**
     * Create connection
     *
     * @return ConnectionInterface
     */
    public function createConnection(): ConnectionInterface;

    /**
     * Get connection from pool
     *
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface;

    /**
     * Release connection to pool
     *
     * @param ConnectionInterface $connection
     */
    public function release(ConnectionInterface $connection): void;

    /**
     * @return int
     */
    public function getConnectionId(): int;

    /**
     * Remove
     */
    public function remove(): void;

    /**
     * Close connections
     *
     * @return int
     */
    public function close(): int;
}
