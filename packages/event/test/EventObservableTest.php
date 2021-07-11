<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event\Test;

use PHPUnit\Framework\TestCase;
use React\EventLoop\StreamSelectLoop;
use Rx\Scheduler;
use Windwalker\Event\EventEmitter;
use Windwalker\Event\EventInterface;
use Windwalker\Promise\Scheduler\EventLoopScheduler;
use Windwalker\Test\Traits\Reactor\SwooleTestTrait;

/**
 * The EventObservableTest class.
 */
class EventObservableTest extends TestCase
{
    use SwooleTestTrait;

    /**
     * @var EventEmitter
     */
    protected $instance;

    public function testObserve(): void
    {
        // $loop = new StreamSelectLoop();
        // $scheduler = new EventLoopScheduler($loop);
        // Scheduler::setDefaultFactory(function () use ($scheduler) {
        //     return $scheduler;
        // });

        $values = [];

        $this->instance->observe('hello')
            ->map(
                static function (EventInterface $event) {
                    $event['num'] += 50;

                    return $event;
                }
            )
            ->map(
                static function (EventInterface $event) {
                    return $event['num'];
                }
            )
            ->subscribe(
                function ($v) use (&$values) {
                    $values[] = $v;

                    if ($v === 55) {
                        // $loop->stop();
                    }
                }
            );

        $this->instance->emit('hello', ['num' => 3]);
        $this->instance->emit('hello', ['num' => 4]);
        $this->instance->emit('hello', ['num' => 5]);

        self::assertEquals([53, 54, 55], $values);
    }

    public function testObserveSwoole(): void
    {
        $loop = new StreamSelectLoop();
        $scheduler = new EventLoopScheduler($loop);
        Scheduler::setDefaultFactory(
            function () use ($scheduler) {
                return $scheduler;
            }
        );

        $values = [];

        $this->instance->observe('hello')
            ->map(
                static function (EventInterface $event) {
                    $event['num'] += 50;

                    return $event;
                }
            )
            ->map(
                static function (EventInterface $event) {
                    return $event['num'];
                }
            )
            ->subscribe(
                function ($v) use (&$values, $loop) {
                    $values[] = $v;

                    if ($v === 55) {
                        $loop->stop();
                    }
                }
            );

        $this->instance->emit('hello', ['num' => 3]);
        $this->instance->emit('hello', ['num' => 4]);
        $this->instance->emit('hello', ['num' => 5]);

        self::assertEquals([53, 54, 55], $values);

        $loop->run();
    }

    protected function setUp(): void
    {
        $this->instance = new EventEmitter();
    }

    protected function tearDown(): void
    {
    }
}
