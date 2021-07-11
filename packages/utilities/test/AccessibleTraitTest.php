<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

namespace Windwalker\Utilities\Test;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Accessible\AccessibleTrait;
use Windwalker\Utilities\Test\Stub\StubAccessible;

/**
 * The AccessibleTraitTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class AccessibleTraitTest extends TestCase
{
    /**
     * @var StubAccessible
     */
    protected $instance;

    /**
     * @see  AccessibleTrait::set
     */
    public function testSet(): void
    {
        $this->instance->set('foo', 123);

        self::assertEquals(123, $this->instance['foo']);
    }

    /**
     * @see  AccessibleTrait::jsonSerialize
     */
    public function testJsonSerialize(): void
    {
        self::assertEquals('[1,2,3]', json_encode($this->instance));
    }

    /**
     * @see  AccessibleTrait::remove
     */
    public function testRemove(): void
    {
        $this->instance->remove(2);

        self::assertEquals([1, 2], $this->instance->dump());
    }

    /**
     * @see  AccessibleTrait::reset
     */
    public function testReset(): void
    {
        $this->instance->reset([1, 1, 1]);

        self::assertEquals([1, 1, 1], $this->instance->dump());
    }

    /**
     * @see  AccessibleTrait::__unset
     */
    public function testMagicUnset(): void
    {
        unset($this->instance->{1});

        self::assertNull($this->instance[1]);
    }

    /**
     * @see  AccessibleTrait::__set
     */
    public function testMagicSet(): void
    {
        $this->instance->foo = 'qwe';

        self::assertEquals([1, 2, 3, 'foo' => 'qwe'], $this->instance->dump());
    }

    /**
     * @see  AccessibleTrait::isNull
     */
    public function testIsNull(): void
    {
        self::assertFalse($this->instance->isNull());

        $this->instance->reset();

        self::assertTrue($this->instance->isNull());
    }

    /**
     * @see  AccessibleTrait::count
     */
    public function testCount(): void
    {
        self::assertEquals(3, $this->instance->count());

        $this->instance[] = 4;

        self::assertCount(4, $this->instance);
    }

    /**
     * @see  AccessibleTrait::offsetUnset
     */
    public function testOffsetUnset(): void
    {
        unset($this->instance[2]);

        self::assertEquals([1, 2], $this->instance->dump());
    }

    /**
     * @see  AccessibleTrait::def
     */
    public function testDef(): void
    {
        $this->instance->def(1, 'qwe');
        $this->instance->def(3, 'flower');

        self::assertEquals([1, 2, 3, 'flower'], $this->instance->dump());
    }

    /**
     * @see  AccessibleTrait::dump
     */
    public function testDump(): void
    {
        self::assertEquals([1, 2, 3], $this->instance->dump());

        $this->instance->reset(
            [
                1,
                2,
                (object) ['foo' => 'bar'],
                new ArrayObject(['flower' => 'sakura']),
                new StubAccessible(['car' => 'jeep']),
            ]
        );

        // Dump recursively
        self::assertEquals(
            [
                1,
                2,
                ['foo' => 'bar'],
                ['flower' => 'sakura'],
                ['car' => 'jeep'],
            ],
            $this->instance->dump(true)
        );

        // Dump recursively but only DumpableInterface will dump to array
        self::assertEquals(
            [
                1,
                2,
                (object) ['foo' => 'bar'],
                new ArrayObject(['flower' => 'sakura']),
                ['car' => 'jeep'],
            ],
            $this->instance->dump(true, true)
        );
    }

    /**
     * @see  AccessibleTrait::notNull
     */
    public function testNotNull(): void
    {
        self::assertTrue($this->instance->notNull());

        $this->instance->reset();

        self::assertFalse($this->instance->notNull());
    }

    /**
     * @see  AccessibleTrait::get
     */
    public function testGet(): void
    {
        $v = &$this->instance->get(1);

        $this->instance->set(1, 'qqq');

        self::assertEquals('qqq', $v);
    }

    /**
     * @see  AccessibleTrait::__get
     */
    public function testMagicGet(): void
    {
        $v = &$this->instance->{1};

        $this->instance->set(1, 'qqq');

        self::assertEquals('qqq', $v);
    }

    /**
     * @see  AccessibleTrait::has
     */
    public function testHas(): void
    {
        self::assertTrue($this->instance->has(1));
        self::assertFalse($this->instance->has(5));
    }

    /**
     * @see  AccessibleTrait::getIterator
     */
    public function testGetIterator(): void
    {
        foreach ($this->instance as &$v) {
            $v++;
        }

        self::assertEquals([2, 3, 4], iterator_to_array($this->instance));
    }

    /**
     * @see  AccessibleTrait::offsetSet
     */
    public function testOffsetSet(): void
    {
        $this->instance[3] = 'flower';

        self::assertEquals([1, 2, 3, 'flower'], $this->instance->dump());
    }

    /**
     * @see  AccessibleTrait::offsetExists
     */
    public function testOffsetExists(): void
    {
        self::assertTrue(isset($this->instance[1]));
        self::assertFalse(isset($this->instance[6]));
    }

    /**
     * @see  AccessibleTrait::offsetGet
     */
    public function testOffsetGet(): void
    {
        $v = &$this->instance[1];

        $this->instance[1] = 'abc';

        self::assertEquals('abc', $v);
    }

    /**
     * @see  AccessibleTrait::__isset
     */
    public function testMagicIsset(): void
    {
        self::assertTrue(isset($this->instance->{1}));
        self::assertFalse(isset($this->instance->{6}));
    }

    protected function setUp(): void
    {
        $this->instance = new StubAccessible([1, 2, 3]);
    }

    protected function tearDown(): void
    {
    }
}
