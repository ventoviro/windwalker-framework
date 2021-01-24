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
     * Create connection
     */
    public function connect(): void;

    /**
     * Reconnect connection
     */
    public function reconnect(): bool;

    /**
     * Get connection id
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Release connection
     *
     * @param bool $force
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
     * @param bool $release
     */
    public function setRelease(bool $release): void;

    /**
     * @param string $poolName
     */
    public function setPoolName(string $poolName): void;

    /**
     * Close connection
     */
    public function disconnect(): void;
}
