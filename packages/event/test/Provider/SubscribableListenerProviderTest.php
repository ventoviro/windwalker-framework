<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event\Test\Provider;

use Closure;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\Attributes\EventSubscriber;
use Windwalker\Event\Attributes\ListenTo;
use Windwalker\Event\EventInterface;
use Windwalker\Event\Listener\ListenerCallable;
use Windwalker\Event\Listener\ListenersQueue;
use Windwalker\Event\Provider\SubscribableListenerProvider;
use Windwalker\Utilities\TypeCast;

/**
 * The StandardListenerProviderTest class.
 */
class SubscribableListenerProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var SubscribableListenerProvider
     */
    protected $instance;

    /**
     * @see  SubscribableListenerProvider::subscribe
     */
    public function testSubscribe(): void
    {
        // phpcs:disable
        $subscriber = new
        #[EventSubscriber]
        // phpcs:enable
        class {
            #[ListenTo('flower.sakura')]
            public function onFlowerSakura(
                $event
            ) {
                $event->foo(2);
            }

            #[ListenTo('flower.olive', 30)]
            #[ListenTo('flower.rose', 50)]
            public function onFlowerRose(
                $event
            ) {
                $event->foo(3);
            }

            #[ListenTo('flower.olive', 30)]
            public function onFlowerOlive(
                $event
            ) {
                $event->foo(1);
            }
        };

        $this->instance->subscribe($subscriber);

        $event = Mockery::mock(EventInterface::class);
        $event->shouldReceive('getName')->andReturn('flower.sakura')->getMock();
        $event->shouldReceive('foo')->with(2)->getMock();

        TypeCast::toArray($this->instance->getListenersForEvent($event))[0]($event);

        $event = Mockery::mock(EventInterface::class);
        $event->shouldReceive('getName')->andReturn('flower.rose')->getMock();
        $event->shouldReceive('foo')->with(3)->getMock();

        TypeCast::toArray($this->instance->getListenersForEvent($event))[0]($event);

        $event = Mockery::mock(EventInterface::class);
        $event->shouldReceive('getName')->andReturn('flower.olive')->getMock();

        /** @var ListenerCallable[] $listeners */
        $listeners = array_values(TypeCast::toArray($this->instance->getListenersForEvent($event)));

        self::assertSame($subscriber, $listeners[0][0]);
        self::assertEquals('onFlowerRose', $listeners[0][1]);
        self::assertEquals('onFlowerOlive', $listeners[1][1]);
    }

    /**
     * @see  SubscribableListenerProvider::on
     * @see  SubscribableListenerProvider::getListenersForEvent
     */
    public function testGetListenersForEvent(): void
    {
        $event = Mockery::mock(EventInterface::class);
        $event->shouldReceive('getName')->andReturn('HelloEvent');
        $event->shouldReceive('hello');

        $this->instance->on(
            $event->getName(),
            $expt = function ($event) {
                $event->hello();
            }
        );

        $handlers = TypeCast::toArray($this->instance->getListenersForEvent($event));

        self::assertSame($expt, $handlers[0]);

        $handlers[0]($event);
    }

    /**
     * @see  SubscribableListenerProvider::getQueues
     */
    public function testGetListeners(): void
    {
        $this->instance->on('hello', $fn1 = $this->nope());
        $this->instance->on('hello', $fn2 = $this->nope());
        $this->instance->on('world', $fn3 = $this->nope());

        $listeners = $this->instance->getQueues();

        self::assertInstanceOf(ListenersQueue::class, $listeners['hello']);
        self::assertInstanceOf(ListenersQueue::class, $listeners['world']);
        self::assertSame($fn2, array_values(TypeCast::toArray($listeners['hello']))[1]);
        self::assertSame($fn3, array_values(TypeCast::toArray($listeners['world']))[0]);
    }

    protected function setUp(): void
    {
        $this->instance = new SubscribableListenerProvider();
    }

    protected function tearDown(): void
    {
        //
    }

    protected function nope(): Closure
    {
        return function () {
            //
        };
    }
}
