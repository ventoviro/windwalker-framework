<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Event\Test;

use Closure;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\Attributes\EventSubscriber;
use Windwalker\Event\Attributes\ListenTo;
use Windwalker\Event\EventEmitter;
use Windwalker\Event\EventInterface;
use Windwalker\Utilities\Accessible\BaseAccessibleTrait;

/**
 * The AttributeSubscriberTest class.
 */
class AttributeSubscriberTest extends TestCase
{
    use BaseAccessibleTrait;

    protected ?EventEmitter $instance = null;

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

    public function testSubscribeStatic()
    {
        $subscriber = $this->getCounterSubscriber();

        $this->instance->subscribe($subscriber);

        $this->instance->emit('staticCount');

        self::assertEquals(1, $subscriber::$staticCount);
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

    protected function getCounterSubscriber(): object
    {
        // phpcs:disable
        return new
        #[EventSubscriber]
        // phpcs:enable
        class {
            public int $count = 0;

            public string $flower = '';

            public static int $staticCount = 0;

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

            #[ListenTo('staticCount')]
            public static function staticCount(): void
            {
                static::$staticCount++;
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
