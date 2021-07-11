<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool\Stack;

use SplStack;
use Windwalker\Pool\ConnectionInterface;
use Windwalker\Pool\Exception\ConnectionPoolException;

/**
 * The PhpStack class.
 */
class PhpStack implements StackInterface
{
    protected ?SplStack $stack;

    /**
     * PhpStack constructor.
     *
     * @param  SplStack|null  $stack
     */
    public function __construct(?SplStack $stack = null)
    {
        $this->stack = $stack ?? new SplStack();
    }

    /**
     * @inheritDoc
     */
    public function push(ConnectionInterface $connection): void
    {
        $this->stack->push($connection);
    }

    /**
     * @inheritDoc
     */
    public function pop(?int $timeout = null): ConnectionInterface
    {
        if ($this->count() === 0) {
            throw new ConnectionPoolException('No connections in pool');
        }

        return $this->stack->pop();
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->stack);
    }

    /**
     * @inheritDoc
     */
    public function waitingCount(): int
    {
        return 0;
    }
}
