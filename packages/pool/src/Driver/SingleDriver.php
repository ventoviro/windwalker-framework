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
 * The BaseDriver class.
 */
class SingleDriver implements DriverInterface
{
    protected mixed $connection = null;

    /**
     * @inheritDoc
     */
    public function push(mixed $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function pop(): mixed
    {
        return $this->connection;
    }
}
