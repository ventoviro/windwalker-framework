<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool\Stack;

use Countable;
use Windwalker\Pool\ConnectionInterface;

/**
 * Interface DriverInterface
 */
interface StackInterface extends Countable
{
    /**
     * Push a connection into pool.
     *
     * @param  ConnectionInterface  $connection
     *
     * @return  void
     */
    public function push(ConnectionInterface $connection): void;

    /**
     * Pop a connection from pool.
     *
     * @param  int|null  $timeout
     *
     * @return  ConnectionInterface
     */
    public function pop(?int $timeout = null): ConnectionInterface;

    /**
     * Count stack.
     *
     * @return  int
     */
    public function count(): int;

    /**
     * Count all waiting consumers.
     *
     * @return  int
     */
    public function waitingCount(): int;
}
