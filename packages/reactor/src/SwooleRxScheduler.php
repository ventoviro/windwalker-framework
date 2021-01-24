<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Reactor;

use Rx\Disposable\CallbackDisposable;
use Rx\Scheduler\EventLoopScheduler;
use Swoole\Timer;

/**
 * The SwooleRxLooper class.
 */
final class SwooleRxScheduler
{
    /**
     * createLoop
     *
     * @return  callable
     */
    public static function createLoop(): callable
    {
        return static function ($ms, $callable) {
            $timer = Timer::after($ms + 1, $callable);

            return new CallbackDisposable(
                function () use ($timer) {
                    Timer::clear($timer);
                }
            );
        };
    }

    /**
     * createScheduler
     *
     * @return  EventLoopScheduler
     */
    public static function createScheduler(): EventLoopScheduler
    {
        return new EventLoopScheduler(self::createLoop());
    }

    /**
     * createSchedulerFactory
     *
     * @return  callable
     */
    public static function factory(): callable
    {
        return static function () {
            return self::createScheduler();
        };
    }
}
