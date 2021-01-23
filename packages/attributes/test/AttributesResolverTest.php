<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Attributes\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;
use Windwalker\Attributes\Test\Stub\Attrs\PropWrap;
use Windwalker\Attributes\Test\Stub\Attrs\StrUpper;
use Windwalker\Attributes\Test\Stub\Attrs\StubMemberResolver;
use Windwalker\Attributes\Test\Stub\Attrs\StubWrapper;
use Windwalker\Attributes\Test\Stub\Attrs\ValueImplode;
use Windwalker\Attributes\Test\Stub\StubAccessible;
use Windwalker\Attributes\Test\Stub\StubObject;

use Windwalker\Scalars\StringObject;

use Windwalker\Test\Traits\BaseAssertionTrait;

use function Windwalker\str;

/**
 * The AttributeResolverTest class.
 */
class AttributesResolverTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?AttributesResolver $instance = null;

    public function testRegisterAttribute(): void
    {
        // Test property type
        $this->instance->registerAttribute(StubWrapper::class, \Attribute::TARGET_PROPERTY);

        self::assertTrue(
            $this->instance->hasAttribute(StubWrapper::class)
        );

        self::assertFalse(
            $this->instance->hasAttribute(StubWrapper::class, \Attribute::TARGET_CLASS)
        );

        // Test add class type
        $this->instance->registerAttribute(StubWrapper::class, \Attribute::TARGET_CLASS);

        self::assertTrue(
            $this->instance->hasAttribute(StubWrapper::class, \Attribute::TARGET_CLASS)
        );

        // Test remove property type
        $this->instance->removeAttribute(StubWrapper::class, \Attribute::TARGET_PROPERTY);

        self::assertFalse(
            $this->instance->hasAttribute(StubWrapper::class, \Attribute::TARGET_PROPERTY)
        );

        self::assertTrue(
            $this->instance->hasAttribute(StubWrapper::class, \Attribute::TARGET_CLASS)
        );

        // Test remove all
        $this->instance->removeAttribute(StubWrapper::class, AttributeType::ALL);

        self::assertFalse(
            $this->instance->hasAttribute(StubWrapper::class)
        );
    }

    public function testRegisterAttributeCallable(): void
    {
        // Test property type
        $this->instance->registerAttribute(StrUpper::class, AttributeType::CALLABLE);

        $foo = new class {
            #[StrUpper]
            public function foo(): string
            {
                return 'foo';
            }
        };

        $r = $this->instance->call([$foo, 'foo']);

        self::assertEquals(
            'FOO',
            $r
        );
    }

    public function testClassCreate()
    {
        $this->instance->registerAttribute(StubWrapper::class, \Attribute::TARGET_CLASS);

        $obj = $this->instance->createObject(StubObject::class);

        self::assertInstanceOf(StubWrapper::class, $obj);
        self::assertInstanceOf(StubObject::class, $obj->instance);
    }

    public function testClassCreateWithNamedArgs()
    {
        $this->instance->registerAttribute(StubWrapper::class, \Attribute::TARGET_CLASS);

        $obj = $this->instance->createObject(
            StubObject::class,
            options: ['foo' => 'bar'],
            stub: new StubAccessible()
        );

        self::assertInstanceOf(StubWrapper::class, $obj);
        self::assertInstanceOf(StubObject::class, $obj->instance);
        self::assertInstanceOf(StubAccessible::class, $obj->instance->stub);
        self::assertEquals(['foo' => 'bar'], $obj->instance->getOptions());
    }

    public function testObjectDecorate()
    {
        $this->instance->registerAttribute(StubWrapper::class, \Attribute::TARGET_CLASS);

        $obj = $this->instance->decorateObject(new StubObject());

        self::assertInstanceOf(StubWrapper::class, $obj);
        self::assertInstanceOf(StubObject::class, $obj->instance);

        $obj = $this->instance->decorateObject(
            new #[StubWrapper] class {
                public $foo = 'bar';
            }
        );

        self::assertInstanceOf(StubWrapper::class, $obj);
        self::assertEquals('bar', $obj->instance->foo);
    }

    public function testResolveObjectMembers()
    {
        $this->instance->registerAttribute(StubMemberResolver::class);

        $foo = new class {
            #[StubMemberResolver]
            public const FLOWER = 'sakura';

            #[StubMemberResolver]
            protected ?string $foo = 'bar';

            public array $output = [];

            #[StubMemberResolver]
            protected function hello(): string
            {
                return '';
            }
        };

        $foo = $this->instance->resolveObjectMembers($foo);

        self::assertStringSafeEquals(
            <<<EOT
ReflectionClassConstant = FLOWER
ReflectionMethod = hello
ReflectionProperty = foo
EOT,
            implode("\n", $foo->output)
        );

    }

    public function testMethocCall()
    {
        $this->instance->registerAttribute(StrUpper::class, \Attribute::TARGET_PARAMETER);
        $this->instance->registerAttribute(ValueImplode::class, AttributeType::CALLABLE);

        $closure = #[ValueImplode(' ')]
        function (
            #[StrUpper]
            string $str = 'hello',
            #[StrUpper]
            StringObject $str2 = null
        ) {
            return [$str, (string) $str2];
        };

        $r = $this->instance->call($closure, [
            'str2' => str('world')
        ]);

        self::assertEquals('HELLO WORLD', $r);
    }

    public function testMethocCallWithReference()
    {
        $this->instance->registerAttribute(StrUpper::class, \Attribute::TARGET_PARAMETER);
        $this->instance->registerAttribute(ValueImplode::class, \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION);

        $opt = [
            'test' => 1
        ];

        $closure = function (array &$options = []) {
            $options['foo'] = 'bar';
        };

        $r = $this->instance->call($closure, [
            'options' => &$opt
        ]);

        self::assertEquals('bar', $opt['foo']);
    }

    public function testProperties()
    {
        $this->instance->registerAttribute(PropWrap::class, \Attribute::TARGET_PROPERTY);

        $obj = new class {
            #[PropWrap(StringObject::class)]
            protected $foo = 'bar';

            public function getFoo()
            {
                return $this->foo;
            }
        };

        $obj = $this->instance->resolveProperties($obj);

        self::assertEquals(
            'BAR',
            $obj->getFoo()->toUpperCase()->__toString()
        );
    }

    // public function testResolveMethods()
    // {
    //     $this->instance->registerAttribute(StubSubscribe::class, \Attribute::TARGET_METHOD);
    //     $this->instance->resolveMethods();
    // }

    protected function setUp(): void
    {
        $this->instance = new AttributesResolver();
    }
}
