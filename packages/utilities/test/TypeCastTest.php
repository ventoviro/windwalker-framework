<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use ArrayIterator;
use ArrayObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\TypeCast;

/**
 * The ArrayHelperTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class TypeCastTest extends TestCase
{
    use BaseAssertionTrait;

    /**
     * testToArray
     *
     * @param $input
     * @param $recursive
     * @param $expect
     *
     * @return  void
     *
     * @dataProvider  providerTestToArray
     */
    public function testToArray($input, $recursive, $expect)
    {
        $this->assertEquals($expect, TypeCast::toArray($input, $recursive));
    }

    /**
     * Data provider for object inputs
     *
     * @return  array
     *
     * @since   2.0
     */
    public function providerTestToArray(): array
    {
        return [
            'string' => [
                'foo',
                false,
                ['foo'],
            ],
            'array' => [
                ['foo'],
                false,
                ['foo'],
            ],
            'array_recursive' => [
                [
                    'foo' => [
                        (object) ['bar' => 'bar'],
                        (object) ['baz' => 'baz'],
                    ],
                ],
                true,
                [
                    'foo' => [
                        ['bar' => 'bar'],
                        ['baz' => 'baz'],
                    ],
                ],
            ],
            'iterator' => [
                ['foo' => new ArrayIterator(['bar' => 'baz'])],
                true,
                ['foo' => ['bar' => 'baz']],
            ],
        ];
    }

    /**
     * testToObject
     *
     * @param  mixed   $input
     * @param  mixed   $expect
     * @param  bool    $recursive
     * @param  string  $message
     *
     * @return  void
     *
     * @dataProvider providerTestToObject
     */
    public function testToObject($input, $expect, bool $recursive, string $message)
    {
        self::assertEquals($expect, TypeCast::toObject($input, $recursive), $message);
    }

    /**
     * providerTestToObject
     *
     * @return  array
     */
    public function providerTestToObject(): array
    {
        return [
            'single object' => [
                [
                    'integer' => 12,
                    'float' => 1.29999,
                    'string' => 'A Test String',
                ],
                (object) [
                    'integer' => 12,
                    'float' => 1.29999,
                    'string' => 'A Test String',
                ],
                false,
                'Should turn array into single object',
            ],
            'multiple objects' => [
                [
                    'first' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'second' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'third' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                ],
                (object) [
                    'first' => (object) [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'second' => (object) [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'third' => (object) [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                ],
                true,
                'Should turn multiple dimension array into nested objects',
            ],
            'single object with class' => [
                [
                    'integer' => 12,
                    'float' => 1.29999,
                    'string' => 'A Test String',
                ],
                (object) [
                    'integer' => 12,
                    'float' => 1.29999,
                    'string' => 'A Test String',
                ],
                false,
                'Should turn array into single object',
            ],
            'multiple objects with class' => [
                [
                    'first' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'second' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'third' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                ],
                (object) [
                    'first' => (object) [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'second' => (object) [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'third' => (object) [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                ],
                true,
                'Should turn multiple dimension array into nested objects',
            ],
        ];
    }

    public function testMapAs(): void
    {
        $src = [
            [
                1,
                2,
                3,
            ],
            [
                4,
                5,
                6,
            ],
        ];

        /** @var ArrayObject[] $r */
        $r = TypeCast::mapAs($src, ArrayObject::class);

        self::assertInstanceOf(ArrayObject::class, $r[0]);
        self::assertInstanceOf(ArrayObject::class, $r[1]);
        self::assertEquals([4, 5, 6], $r[1]->getArrayCopy());
    }

    /**
     * @param  mixed   $value
     * @param  mixed   $expt
     * @param  string  $type
     *
     * @see          TypeCast::try()
     *
     * @dataProvider providerTry
     */
    public function testTry($value, $expt, string $type): void
    {
        if ($type === 'object') {
            self::assertEquals($expt, TypeCast::try($value, $type, false));
        } else {
            self::assertSame($expt, TypeCast::try($value, $type, false));
        }
    }

    public function providerTry(): array
    {
        return [
            // To int
            [
                'foo',
                0,
                'int',
            ],
            [
                '3',
                3,
                'int',
            ],
            [
                '10.0',
                10,
                'int',
            ],
            [
                10.0,
                10,
                'int',
            ],
            [
                10.3,
                10,
                'int',
            ],
            [
                [],
                null,
                'int',
            ],
            [
                new stdClass(),
                null,
                'int',
            ],
            [
                true,
                1,
                'int',
            ],
            [
                false,
                0,
                'int',
            ],
            // To float
            [
                'foo',
                0.0,
                'float',
            ],
            [
                '3',
                3.0,
                'float',
            ],
            [
                '10.0',
                10.0,
                'float',
            ],
            [
                10.0,
                10.0,
                'float',
            ],
            [
                10.3,
                10.3,
                'float',
            ],
            [
                [],
                null,
                'float',
            ],
            [
                new stdClass(),
                null,
                'float',
            ],
            [
                true,
                1.0,
                'float',
            ],
            [
                false,
                0.0,
                'float',
            ],
            // To string
            [
                'foo',
                'foo',
                'string',
            ],
            [
                1,
                '1',
                'string',
            ],
            [
                1.23000,
                '1.23',
                'string',
            ],
            [
                [],
                null,
                'string',
            ],
            [
                new stdClass(),
                null,
                'string',
            ],
            [
                75e-5,
                '0.00075',
                'string',
            ],
            // Bool
            [
                'A',
                true,
                'bool',
            ],
            [
                '1',
                true,
                'bool',
            ],
            [
                '',
                false,
                'bool',
            ],
            [
                '0',
                false,
                'bool',
            ],
            [
                0,
                false,
                'bool',
            ],
            // array
            [
                'a',
                ['a'],
                'array',
            ],
            [
                123,
                [123],
                'array',
            ],
            [
                (object) ['foo' => 'bar'],
                ['foo' => 'bar'],
                'array',
            ],
            // obj
            [
                ['foo' => 'bar'],
                (object) ['foo' => 'bar'],
                'object',
            ],
            [
                static function () {
                },
                static function () {
                },
                'object',
            ],
            // Other
            [
                'Hello',
                null,
                'none',
            ],
        ];
    }

    /**
     * @param  mixed   $value
     * @param  mixed   $expt
     * @param  string  $type
     *
     * @see          TypeCast::try()
     *
     * @dataProvider providerTryStrict
     */
    public function testTryStrict($value, $expt, string $type): void
    {
        $failMsg = sprintf(
            'Try convert %s to %s failed',
            TypeAssert::describeValue($value),
            TypeAssert::describeValue($expt)
        );

        if ($type === 'object') {
            self::assertEquals($expt, TypeCast::try($value, $type, true), $failMsg);
        } else {
            self::assertSame($expt, TypeCast::try($value, $type, true), $failMsg);
        }
    }

    public function providerTryStrict(): array
    {
        return [
            // To int
            [
                'foo',
                null,
                'int',
            ],
            [
                '3',
                3,
                'int',
            ],
            [
                '10.0',
                10,
                'int',
            ],
            [
                10.0,
                10,
                'int',
            ],
            [
                10.3,
                null,
                'int',
            ],
            [
                [],
                null,
                'int',
            ],
            [
                new stdClass(),
                null,
                'int',
            ],
            [
                true,
                null,
                'int',
            ],
            [
                false,
                null,
                'int',
            ],
            // To float
            [
                'foo',
                null,
                'float',
            ],
            [
                '3',
                3.0,
                'float',
            ],
            [
                '10.0',
                10.0,
                'float',
            ],
            [
                10.0,
                10.0,
                'float',
            ],
            [
                10.3,
                10.3,
                'float',
            ],
            [
                [],
                null,
                'float',
            ],
            [
                new stdClass(),
                null,
                'float',
            ],
            [
                true,
                null,
                'float',
            ],
            [
                false,
                null,
                'float',
            ],
            // To string
            [
                'foo',
                'foo',
                'string',
            ],
            [
                1,
                '1',
                'string',
            ],
            [
                1.23000,
                '1.23',
                'string',
            ],
            [
                [],
                null,
                'string',
            ],
            [
                new stdClass(),
                null,
                'string',
            ],
            [
                75e-5,
                '0.00075',
                'string',
            ],
        ];
    }

    public function testTryShortCut()
    {
        $r = TypeCast::tryInteger('123');

        self::assertSame(123, $r);
    }
}
