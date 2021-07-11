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
 * Interface ConnectionInterface
 */
interface ConnectionInterface
{
    /**
     * setPool
     *
     * @param  ?PoolInterface  $pool
     *
     * @return  void
     */
    public function setPool(?PoolInterface $pool): void;

    /**
     * Create connection
     *
     * @return mixed
     */
    public function connect(): mixed;

    /**
     * Reconnect connection
     *
     * @return mixed
     */
    public function reconnect(): mixed;

    /**
     * Close connection
     *
     * @return mixed
     */
    public function disconnect(): mixed;

    /**
     * isConnected
     *
     * @return  bool
     */
    public function isConnected(): bool;

    /**
     * Get connection id
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Release connection
     *
     * @param  bool  $force
     */
    public function release(bool $force = false): void;

    /**
     * Get last time
     *
     * @return int
     */
    public function getLastTime(): int;

    /**
     * Update last time
     */
    public function updateLastTime(): void;

    /**
     * Set whether to release
     *
     * @param  bool  $active
     */
    public function setActive(bool $active): void;

    /**
     * Is connection active.
     *
     * @return  bool
     */
    public function isActive(): bool;
}
