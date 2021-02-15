<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Reflection;

use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The ReflectAccessorTest class.
 */
class ReflectAccessorTest extends TestCase
{
    protected ?ReflectAccessor $instance;

    /**
     * @see  ReflectAccessor::setValue
     */
    public function testSetValueWithSafeType(): void
    {
        $foo = new class implements \JsonSerializable {
            protected ?int $id;
            protected int|string|null $price;
            protected int $parentId;
            protected string $content;
            protected $foo;

            public function jsonSerialize(): array
            {
                return get_object_vars($this);
            }
        };

        ReflectAccessor::setValue($foo, 'id', '123', true);
        ReflectAccessor::setValue($foo, 'price', '123', true);
        ReflectAccessor::setValue($foo, 'parentId', 'Test', true);
        ReflectAccessor::setValue($foo, 'content', null, true);
        ReflectAccessor::setValue($foo, 'foo', null, true);

        self::assertEquals(
            '{"id":123,"price":"123","parentId":0,"content":"","foo":null}',
            json_encode($foo)
        );
    }

    /**
     * @see  ReflectAccessor::getValue
     */
    public function testGetValue(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ReflectAccessor::reflect
     */
    public function testReflect(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ReflectAccessor::invoke
     */
    public function testInvoke(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ReflectAccessor::getProperties
     */
    public function testGetProperties(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ReflectAccessor::getReflectProperties
     */
    public function testGetReflectProperties(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ReflectAccessor::getReflectMethods
     */
    public function testGetReflectMethods(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ReflectAccessor::getNoRepeatAttributes
     */
    public function testGetNoRepeatAttributes(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function tearDown(): void
    {
    }
}
