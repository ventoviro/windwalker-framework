<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool\Driver;

use Swoole\Coroutine\Channel;

/**
 * The SwooleDriver class.
 */
class SwooleDriver implements DriverInterface
{
    protected int $maxSize = 1;

    protected ?Channel $channel = null;

    /**
     * SwooleDriver constructor.
     *
     * @param  int  $maxSize
     */
    public function __construct(int $maxSize)
    {
        $this->maxSize = $maxSize;
    }

    /**
     * @inheritDoc
     */
    public function push(mixed $connection): void
    {
        $this->channel ??= new Channel($this->maxSize);

        $this->channel->push($connection);
    }

    /**
     * @inheritDoc
     */
    public function pop(): mixed
    {
        if (!$this->channel) {
            throw new \LogicException('Channel not exists in ' . static::class);
        }

        return $this->channel->pop();
    }
}
