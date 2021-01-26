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
 * The ConnectionPool class.
 */
class ConnectionPool extends AbstractPool
{
    /**
     * @var callable
     */
    protected $builder;

    protected function create(): ConnectionInterface
    {
        return ($this->builder)();
    }

    public function setConnectionBuilder(callable $builder): void
    {
        $this->builder = $builder;
    }
}
