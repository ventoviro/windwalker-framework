<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Assert;

use PHPUnit\Framework\TestCase;
use TypeError;
use Windwalker\Utilities\Assert\Assert;
use Windwalker\Utilities\Assert\TypeAssert;

/**
 * The TypeAssertTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class TypeAssertTest extends TestCase
{
    /**
     * @var Assert
     */
    protected ?Assert $instance;

    public function testStaticAssert()
    {
        try {
            TypeAssert::assert(
                false,
                '{caller} with wrong type %s.',
                123
            );
        } catch (TypeError $e) {
            self::assertEquals(
                'Windwalker\Utilities\Test\Assert\TypeAssertTest::testStaticAssert() with wrong type integer(123).',
                $e->getMessage()
            );
        }
    }

    /**
     * testThrowException
     *
     * @param  string  $class
     * @param  string  $message
     * @param          $value
     * @param  string  $caller
     * @param  string  $expected
     *
     * @return  void
     *
     * @dataProvider providerThrowException
     */
    public function testThrowException(string $class, string $message, $value, ?string $caller, string $expected): void
    {
        try {
            self::createInstance($caller)->throwException($message, $value);
        } catch (TypeError $e) {
            self::assertEquals($expected, $e->getMessage());
        }
    }

    public function providerThrowException(): array
    {
        return [
            'Auto get caller' => [
                TypeError::class,
                'Method {caller} must with type X, %s given.',
                5,
                null,
                'Method Windwalker\Utilities\Test\Assert\TypeAssertTest::testThrowException() ' .
                'must with type X, integer(5) given.',
            ],
            'Custom caller' => [
                TypeError::class,
                'Method {caller} must with type X, %s given.',
                5,
                'Foo::bar()',
                'Method Foo::bar() must with type X, integer(5) given.',
            ],
            'Custom arguments ordering' => [
                TypeError::class,
                'Got %s in {caller}',
                5,
                'Foo::bar()',
                'Got integer(5) in Foo::bar()',
            ],
            'No message arguments' => [
                TypeError::class,
                'Method wrong.',
                5,
                null,
                'Method wrong.',
            ],
        ];
    }

    protected function setUp(): void
    {
        // $this->instance = new Assert(fn ($msg) => new TypeError($msg));
    }

    protected static function createInstance(?string $caller): Assert
    {
        $caller ??= Assert::getCaller(2);

        return new Assert(fn($msg) => new TypeError($msg), $caller);
    }
}
