<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool\Test\Stub;

use Windwalker\Pool\AbstractPool;
use Windwalker\Pool\ConnectionInterface;

/**
 * The StubConnectionPool class.
 */
class StubConnectionPool extends AbstractPool
{
    /**
     * @inheritDoc
     */
    protected function create(): ConnectionInterface
    {
        return new StubConnection();
    }
}
