<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool\Stack;

use Windwalker\Pool\ConnectionInterface;

/**
 * The BaseDriver class.
 */
class SingleStack implements StackInterface
{
    protected mixed $connection = null;

    /**
     * @inheritDoc
     */
    public function push(ConnectionInterface $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function pop(?int $timeout = null): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return 1;
    }

    /**
     * @inheritDoc
     */
    public function waitingCount(): int
    {
        return 0;
    }
}
