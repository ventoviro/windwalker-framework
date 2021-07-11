<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event\Test;

use Closure;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\Attributes\EventSubscriber;
use Windwalker\Event\Attributes\ListenTo;
use Windwalker\Event\EventDispatcher;
use Windwalker\Event\EventEmitter;
use Windwalker\Event\EventInterface;
use Windwalker\Event\Provider\SimpleListenerProvider;
use Windwalker\Utilities\TypeCast;

use function Windwalker\disposable;

/**
 * The EventEmitterTest class.
 */
class EventEmitterTest extends TestCase
{
    /**
     * @var EventEmitter
     */
    protected $instance;

    /**
     * @see  EventEmitter::getListeners
     */
    public function testGetListeners(): void
    {
        $this->instance->on('hello', $fn1 = $this->nope(1));
        $this->instance->on('hello', $fn2 = $this->nope(2));

        $queue = array_values(TypeCast::toArray($this->instance->getListeners('hello')));

        self::assertSame(1, $queue[0]());
        self::assertSame(2, $queue[1]());
    }

    /**
     * @see  EventEmitter::once
     */
    public function testOnce(): void
    {
        $count = 0;
        $fn = function () use (&$count) {
            $count++;
        };

        $this->instance->on('hello', disposable($fn));

        $this->instance->emit('hello');
        $this->instance->emit('hello');
        $this->instance->emit('hello');

        self::assertEquals(1, $count);
    }

    /**
     * @see  EventEmitter::once
     */
    public function testOnceWithListenerCallable(): void
    {
        $count = 0;
        $fn = disposable(
            static function () use (&$count) {
                $count++;
            }
        );

        $this->instance->on('hello', $fn);

        $this->instance->emit('hello');
        $this->instance->emit('hello');
        $this->instance->emit('hello');

        self::assertEquals(1, $count);
    }

    /**
     * @see  EventEmitter::once
     */
    public function testOnceWithDisposableCallable(): void
    {
        $count = 0;
        $fn = disposable(
            static function () use (&$count) {
                $count++;
            }
        );

        $this->instance->on('hello', $fn);

        $this->instance->emit('hello');
        $this->instance->emit('hello');
        $this->instance->emit('hello');

        self::assertEquals(1, $count);

        // Listener should auto remove
        self::assertCount(0, TypeCast::toArray($this->instance->getListeners('hello')));
    }

    /**
     * @see  EventEmitter::emit
     */
    public function testEmit(): void
    {
        $count = 0;
        $fn = function (EventInterface $event) use (&$count) {
            $event['result'] = $count += $event['num'];
        };

        $this->instance->on('count', $fn);

        $event1 = $this->instance->emit('count', ['num' => 2]);
        $event2 = $this->instance->emit('count', ['num' => 5]);

        self::assertEquals(7, $count);
        self::assertEquals(2, $event1['result']);
        self::assertEquals(7, $event2['result']);
    }

    public function testSubscribe()
    {
        $subscriber = $this->getOnceSubscriber();

        $this->instance->subscribe($subscriber);

        $this->instance->emit('foo', ['num' => 2]);
        $this->instance->emit('foo', ['num' => 2]);
        $this->instance->emit('foo', ['num' => 2]);

        self::assertEquals(2, $subscriber->count);

        $this->instance->emit('bar', ['num' => 3]);
        $this->instance->emit('bar', ['num' => 4]);

        self::assertEquals(72, $subscriber->count);
    }

    public function testSubscribeSimpleObject(): void
    {
        $subscriber = new class {
            public $count = 0;

            public function foo(EventInterface $event): void
            {
                $this->count += $event['num'];
            }

            public function bar(EventInterface $event): void
            {
                $this->count *= $event['num'];
            }
        };

        $this->instance->subscribe($subscriber);

        $this->instance->emit('foo', ['num' => 2]);
        $this->instance->emit('foo', ['num' => 2]);
        $this->instance->emit('foo', ['num' => 2]);

        self::assertEquals(6, $subscriber->count);

        $this->instance->emit('bar', ['num' => 3]);
        $this->instance->emit('bar', ['num' => 4]);

        self::assertEquals(72, $subscriber->count);
    }

    /**
     * @see  EventEmitter::off
     */
    public function testOff(): void
    {
        $count = 0;
        $fn = function (EventInterface $event) use (&$count) {
            $event['result'] = $count += $event['num'];
        };

        $this->instance->on('count', $fn);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);

        $this->instance->off('count');

        $this->instance->emit('count', ['num' => 3]);
        $this->instance->emit('count', ['num' => 6]);

        self::assertEquals(7, $count);
    }

    /**
     * @see  EventEmitter::off
     */
    public function testOffClosure(): void
    {
        $count = 0;
        $fn1 = function (EventInterface $event) use (&$count) {
            $event['result'] = $count += $event['num'];
        };
        $fn2 = function (EventInterface $event) use (&$count) {
            $event['result'] = $count += $event['num'];
        };

        $this->instance->on('count', $fn1);
        $this->instance->on('count', $fn2);

        $this->instance->off('count', $fn2);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);

        self::assertEquals(7, $count);
    }

    /**
     * @see  EventEmitter::off
     */
    public function testOffCallable(): void
    {
        $subscriber = $this->getCounterSubscriber();

        $this->instance->subscribe($subscriber);

        $this->instance->off('count', [$subscriber, 'count1']);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);
        $this->instance->emit('flower', ['num' => 10]);

        self::assertEquals(7, $subscriber->count);
        self::assertEquals('Sakura', $subscriber->flower);
    }

    /**
     * @see  EventEmitter::off
     */
    public function testOffSubscriber(): void
    {
        $subscriber = $this->getCounterSubscriber();

        $this->instance->subscribe($subscriber);

        $this->instance->off('count', $subscriber);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);

        self::assertEquals(0, $subscriber->count);
    }

    /**
     * @see  EventEmitter::off
     */
    public function testOffSubscriberAsClosure(): void
    {
        $subscriber = $this->getCounterSubscriber();

        $this->instance->on('count', Closure::fromCallable([$subscriber, 'count1']));
        $this->instance->on('count', Closure::fromCallable([$subscriber, 'count2']));

        $this->instance->off('count', $subscriber);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);

        self::assertEquals(0, $subscriber->count);
    }

    /**
     * @see  EventEmitter::on
     */
    public function testOn(): void
    {
        $values = [];

        $this->instance->on(
            'foo',
            function () use (&$values) {
                $values[] = 500;
            },
            500
        );

        $this->instance->on(
            'foo',
            function () use (&$values) {
                $values[] = 300;
            },
            300
        );

        $this->instance->on(
            'foo',
            function () use (&$values) {
                $values[] = 700;
            },
            700
        );

        $this->instance->emit('foo');

        self::assertEquals([700, 500, 300], $values);
    }

    /**
     * @see  EventEmitter::__construct
     */
    public function testConstruct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  EventEmitter::remove
     */
    public function testRemoveCallable(): void
    {
        $subscriber = $this->getCounterSubscriber();

        $this->instance->on('flower', [$subscriber, 'count1']);

        $this->instance->subscribe($subscriber);

        $this->instance->remove([$subscriber, 'count1']);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);
        $this->instance->emit('flower', ['num' => 5]);

        self::assertEquals(7, $subscriber->count);
        self::assertEquals('Sakura', $subscriber->flower);
    }

    /**
     * @see  EventEmitter::remove
     */
    public function testRemoveSubscriber(): void
    {
        $subscriber = $this->getCounterSubscriber();

        $this->instance->on('flower', [$subscriber, 'count1']);

        $this->instance->subscribe($subscriber);

        $this->instance->remove($subscriber);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);
        $this->instance->emit('flower', ['num' => 5]);

        self::assertEquals(0, $subscriber->count);
        self::assertEquals('', $subscriber->flower);
    }

    /**
     * @see  EventEmitter::setQueueHolder
     */
    public function testSetQueueHolder(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  EventEmitter::getQueueHolder
     */
    public function testGetQueueHolder(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function getCounterSubscriber(): object
    {
        // phpcs:disable
        return new
        #[EventSubscriber]
        // phpcs:enable
        class {
            public $count = 0;

            public $flower = '';

            #[ListenTo('count')]
            public function count1(
                EventInterface $event
            ): void {
                $event['result'] = $this->count += $event['num'];
            }

            #[ListenTo('count')]
            public function count2(
                EventInterface $event
            ): void {
                $event['result'] = $this->count += $event['num'];
            }

            #[ListenTo('flower')]
            public function sakura()
            {
                $this->flower = 'Sakura';
            }
        };
    }

    protected function getOnceSubscriber(): object
    {
        // phpcs:disable
        return new
        #[EventSubscriber]
        // phpcs:enable
        class {
            public $count = 0;

            #[ListenTo('foo', 500, true)]
            public function foo(
                EventInterface $event
            ) {
                $this->count += $event['num'];
            }

            #[ListenTo('bar', 100, true)]
            public function bar1(
                EventInterface $event
            ) {
                $this->count *= $event['num'];
            }

            #[ListenTo('bar', 100)]
            public function bar2(
                EventInterface $event
            ) {
                $this->count *= $event['num'];
            }
        };
    }

    public function testObserve(): void
    {
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
                static function ($v) use (&$values) {
                    $values[] = $v;
                }
            );

        $this->instance->emit('hello', ['num' => 3]);
        $this->instance->emit('hello', ['num' => 4]);
        $this->instance->emit('hello', ['num' => 5]);

        self::assertEquals([53, 54, 55], $values);
    }

    public function testAppendProvider(): void
    {
        $this->instance->on(
            'hello',
            function (EventInterface $event) {
                $event['main'] = true;
            }
        );

        $provider1 = new SimpleListenerProvider(
            [
                'hello' => [
                    function (EventInterface $event) {
                        $event['sub1'] = true;
                    },
                ],
            ]
        );

        $provider2 = new SimpleListenerProvider(
            [
                'hello' => [
                    function (EventInterface $event) {
                        $event['sub2'] = true;
                    },
                ],
            ]
        );

        $this->instance->appendProvider($provider1)
            ->appendProvider($provider2);

        $event = $this->instance->emit('hello');

        self::assertEquals(['main', 'sub1', 'sub2'], array_keys($event->getArguments()));
    }

    public function testRegisterDealer(): void
    {
        $this->instance->on(
            'hello',
            function (EventInterface $event) {
                $event['main'] = true;
            }
        );

        $dealer1 = new EventDispatcher(
            new SimpleListenerProvider(
                [
                    'hello' => [
                        function (EventInterface $event) {
                            $event['sub1'] = true;
                        },
                    ],
                ]
            )
        );

        $dealer2 = new EventDispatcher(
            new SimpleListenerProvider(
                [
                    'hello' => [
                        function (EventInterface $event) {
                            $event['sub2'] = true;
                        },
                    ],
                ]
            )
        );

        $this->instance->addDealer($dealer1)
            ->addDealer($dealer2);

        $event = $this->instance->emit('hello');

        self::assertEquals(['main', 'sub1', 'sub2'], array_keys($event->getArguments()));
    }

    protected function setUp(): void
    {
        $this->instance = new EventEmitter();
    }

    protected function tearDown(): void
    {
    }

    protected function nope($value = null): Closure
    {
        return function () use ($value) {
            return $value;
        };
    }
}
