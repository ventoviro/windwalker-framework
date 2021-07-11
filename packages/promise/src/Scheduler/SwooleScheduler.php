<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Scheduler;

use LogicException;
use Swoole\Coroutine\Channel;
use Swoole\Event;

/**
 * The SwooleAsync class.
 */
class SwooleScheduler implements SchedulerInterface
{
    /**
     * @var int|null
     */
    protected ?int $timeout;

    /**
     * SwooleAsync constructor.
     *
     * @param  int|null  $timeout
     */
    public function __construct(?int $timeout = null)
    {
        $this->timeout = $timeout;
    }

    /**
     * @inheritDoc
     */
    public static function isSupported(): bool
    {
        return extension_loaded('swoole') && function_exists('\go');
    }

    /**
     * @inheritDoc
     */
    public function schedule(callable $callback): ScheduleCursor
    {
        Event::defer(
            static function () use ($callback) {
                go(
                    static function () use ($callback) {
                        $callback();
                    }
                );
            }
        );

        // Return Channel as cursor, when Promise resolved,
        // it will call SwooleAsync::done() to push value into Channel.
        return new ScheduleCursor(new Channel());
    }

    /**
     * @inheritDoc
     */
    public function wait(ScheduleCursor $cursor): void
    {
        $chan = $cursor->get();

        if (!$chan instanceof Channel) {
            throw new LogicException('Cursor should be ' . Channel::class);
        }

        // SwooleAsync should always called into coroutine,
        // so we don't need to create new coroutine to wrap $chan->pop().
        // This can make current coroutine blocked and wait for IO end.
        if ($this->timeout) {
            $chan->pop($this->timeout);
        } else {
            $chan->pop();
        }
    }

    /**
     * @inheritDoc
     */
    public function done(?ScheduleCursor $cursor): void
    {
        $chan = $cursor->get();

        if (!$chan instanceof Channel) {
            throw new LogicException('Cursor should be ' . Channel::class);
        }

        // Done() may be called at next event loop,
        // We create a new coroutine to make sure it always in coroutine context.
        go(
            static function () use ($chan) {
                $chan->push(true);
            }
        );
    }
}
