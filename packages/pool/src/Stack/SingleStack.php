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
use Windwalker\Pool\Exception\ConnectionPoolException;

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
        if (!$this->connection) {
            throw new ConnectionPoolException('No connection exists, must push one into stack first.');
        }

        $conn = $this->connection;

        $this->connection = null;

        return $conn;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->connection ? 1 : 0;
    }

    /**
     * @inheritDoc
     */
    public function waitingCount(): int
    {
        return 0;
    }
}
