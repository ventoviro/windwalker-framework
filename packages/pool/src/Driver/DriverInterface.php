<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool\Driver;

/**
 * Interface DriverInterface
 */
interface DriverInterface
{
    /**
     * Push a connection into pool.
     *
     * @param  mixed  $connection
     *
     * @return  void
     */
    public function push(mixed $connection): void;

    /**
     * Pop a connection from pool.
     *
     * @return  mixed
     */
    public function pop(): mixed;
}
