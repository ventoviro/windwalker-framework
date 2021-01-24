<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Test;

use Attribute;
use PHPUnit\Framework\TestCase;
use Windwalker\DI\Attributes\AttributeType;
use Windwalker\DI\Attributes\Autowire;
use Windwalker\DI\Attributes\Decorator;
use Windwalker\DI\Container;
use Windwalker\DI\Test\Injection\Attrs\ParamLower;
use Windwalker\DI\Test\Injection\Attrs\ToUpper;
use Windwalker\DI\Test\Injection\InnerStub;
use Windwalker\DI\Test\Injection\StubService;
use Windwalker\DI\Test\Injection\WiredClass;
use Windwalker\DI\Test\Injection\Wrapped;
use Windwalker\Scalars\StringObject;

use function Windwalker\str;

/**
 * The AttributeTest class.
 */
class AttributeTest extends TestCase
{
    protected ?Container $instance;

    public function testObjectDecorate()
    {
        $this->instance->getAttributesResolver()
            ->registerAttribute(Decorator::class, Attribute::TARGET_CLASS);

        $result = $this->instance->newInstance(InnerStub::class);

        self::assertInstanceOf(Wrapped::class, $result);
        self::assertInstanceOf(InnerStub::class, $result->instance);
    }

    public function testObjectDecorateCallable()
    {
        $this->instance->getAttributesResolver()
            ->registerAttribute(Decorator::class, Attribute::TARGET_CLASS);

        $result = $this->instance->newInstance(
            function () {
                return new InnerStub();
            }
        );

        self::assertInstanceOf(Wrapped::class, $result);
        self::assertInstanceOf(InnerStub::class, $result->instance);
    }

    public function testObjectWrapCreator()
    {
        $this->instance->getAttributesResolver()
            ->registerAttribute(Autowire::class, Attribute::TARGET_CLASS);

        $result = $this->instance->newInstance(WiredClass::class);

        self::assertInstanceOf(WiredClass::class, $result);
        self::assertInstanceOf(StringObject::class, $result->logs[0]);
    }

    public function testMethodAttributes()
    {
        $this->instance->set('stub', fn() => new StubService());

        $this->instance->getAttributesResolver()
            ->registerAttribute(ToUpper::class, AttributeType::CALLABLE);

        $obj = new class {
            #[ToUpper]
            public function foo(): string
            {
                return 'foo';
            }
        };

        $result = $this->instance->call([$obj, 'foo'], [1, 2, 3]);

        self::assertEquals(
            'FOO',
            $result
        );
    }

    public function testMethodParamAttributes()
    {
        $this->instance->set('stub', fn() => new StubService());

        $this->instance->getAttributesResolver()
            ->registerAttribute(ParamLower::class, Attribute::TARGET_PARAMETER);

        $obj = new class {
            public function foo(
                #[ParamLower]
                StringObject $foo
            ): string {
                return (string) $foo;
            }
        };

        $result = $this->instance->call([$obj, 'foo'], [str('FOO')]);

        self::assertEquals(
            'foo',
            $result
        );
    }

    public function testCallClosure()
    {
        $this->instance->getAttributesResolver()
            ->registerAttribute(Autowire::class, Attribute::TARGET_PARAMETER);

        $closure = function (
            #[Autowire]
            StubService $stub,
            array &$options = []
        ): StubService {
            $options['foo'] = 'bar';

            return $stub;
        };

        $options = [];

        $stub = $this->instance->call($closure, ['options' => &$options]);

        self::assertEquals(
            ['foo' => 'bar'],
            $options
        );
    }

    protected function setUp(): void
    {
        $this->instance = new Container();
    }

    protected function tearDown(): void
    {
    }
}
