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
use Windwalker\Event\Event;

/**
 * The EventTest class.
 */
class EventTest extends TestCase
{
    protected ?Event $instance;

    /**
     * @see  Event::merge
     */
    public function testMerge(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Event::jsonSerialize
     */
    public function testJsonSerialize(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Event::isPropagationStopped
     */
    public function testIsPropagationStopped(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Event::__construct
     */
    public function test__construct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Event::unserialize
     */
    public function testUnserialize(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Event::setArguments
     */
    public function testSetArguments(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Event::mirror
     */
    public function testMirror(): void
    {
        $event = $this->instance->mirror('LiveLong', ['flower' => 'rose']);
        self::assertNotSame($this->instance, $event);
        self::assertEquals(
            'LiveLong',
            $event->getName()
        );

        self::assertEquals(
            [
                'flower' => 'rose',
                'starship' => 'Enterprise',
            ],
            $event->getArguments()
        );
    }

    /**
     * @see  Event::getName
     */
    public function testGetName(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Event::serialize
     */
    public function testSerialize(): void
    {
        $s = serialize($this->instance);

        $event = unserialize($s);

        self::assertEquals(
            'onHelloWelcome',
            $event->getName()
        );

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'starship' => 'Enterprise',
            ],
            $event->getArguments()
        );
    }

    /**
     * @see  Event::getArguments
     */
    public function testGetArguments(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Event::stopPropagation
     */
    public function testStopPropagation(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Event::wrap
     */
    public function testWrap(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Event::clear
     */
    public function testClear(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    public function testGetterSetter()
    {
        $this->instance->setFlower('Olive');
        $flower = $this->instance->getFlower('Olive');

        self::assertEquals('Olive', $flower);
        self::assertEquals(
            [
                'flower' => 'Olive',
                'starship' => 'Enterprise',
            ],
            $this->instance->getArguments()
        );
    }

    protected function setUp(): void
    {
        $this->instance = new Event(
            'onHelloWelcome', [
            'flower' => 'Sakura',
            'starship' => 'Enterprise',
        ]
        );
    }

    protected function tearDown(): void
    {
    }
}
