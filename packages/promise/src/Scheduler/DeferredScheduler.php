<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Scheduler;

use React\EventLoop\LoopInterface;

/**
 * The DeferredAsync class.
 */
class DeferredScheduler implements SchedulerInterface
{
    /**
     * @inheritDoc
     */
    public static function isSupported(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function schedule(callable $callback): ScheduleCursor
    {
        TaskQueue::getInstance()->push($callback);

        return new ScheduleCursor(
            static function () {
                TaskQueue::getInstance()->run();
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function wait(ScheduleCursor $cursor): void
    {
        $cursor->get()();
    }

    /**
     * @inheritDoc
     */
    public function done(?ScheduleCursor $cursor): void
    {
        //
    }

    /**
     * registerEventLoop
     *
     * @param  LoopInterface|callable  $loop
     * @param  bool                    $disableShutdown
     *
     * @return  DeferredScheduler
     */
    public static function registerEventLoop(
        LoopInterface|callable $loop,
        bool $disableShutdown = false
    ): DeferredScheduler {
        if ($disableShutdown) {
            TaskQueue::getInstance()->disableShutdownRunner();
        }

        $callback = $loop instanceof LoopInterface
            ? static function () use ($loop) {
                $loop->addPeriodicTimer(0, [TaskQueue::getInstance(), 'run']);
            }
            : $loop;

        $callback();

        return new static();
    }
}
