<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Scalars\Test\Concern;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Windwalker\Scalars\ArrayObject;

use function Windwalker\arr;

/**
 * The ArrayCreationTraitTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ArrayCreationTraitTest extends TestCase
{
    /**
     * @var  ArrayObject
     */
    protected $instance;

    public function testCombine(): void
    {
        self::assertEquals(
            [
                1 => 'foo',
                2 => 'bar',
                3 => 'yoo',
            ],
            $this->instance->combine(['foo', 'bar', 'yoo'])->dump()
        );

        self::assertEquals(
            [
                1 => 'foo',
                2 => 'bar',
                3 => 'yoo',
            ],
            $this->instance->combine(arr(['foo', 'bar', 'yoo']))->dump()
        );
    }

    public function testMergeRecursive(): void
    {
        $data1 = arr(
            [
                'green' => 'Hulk',
                'red' => 'empty',
                'human' => [
                    'dark' => 'empty',
                    'black' => [
                        'male' => 'empty',
                        'female' => 'empty',
                        'no-gender' => 'empty',
                    ],
                ],
            ]
        );

        $data2 = [
            'ai' => 'Jarvis',
            'agent' => 'Phil Coulson',
            'red' => [
                'left' => 'Pepper',
                'right' => 'Iron Man',
            ],
            'human' => [
                'dark' => 'Nick Fury',
                'black' => [
                    'female' => 'Black Widow',
                    'male' => 'Loki',
                ],
            ],
        ];

        $data3 = arr(
            [
                'ai' => 'Ultron',
            ]
        );

        $expected = [
            'ai' => 'Jarvis',
            'agent' => 'Phil Coulson',
            'green' => 'Hulk',
            'red' => [
                'left' => 'Pepper',
                'right' => 'Iron Man',
            ],
            'human' => [
                'dark' => 'Nick Fury',
                'black' => [
                    'male' => 'Loki',
                    'female' => 'Black Widow',
                    'no-gender' => 'empty',
                ],
            ],
        ];

        $this->assertEquals($expected, $data1->mergeRecursive($data1, $data2)->dump());

        $expected['ai'] = 'Ultron';

        $this->assertEquals($expected, $data1->mergeRecursive($data2, $data3)->dump());

        $this->expectException(InvalidArgumentException::class);

        $data1->mergeRecursive('', 123);
    }

    public function testFillKeys(): void
    {
        $a = $this->instance->fillKeys(['foo', 'bar'], [1, 2, 3]);

        self::assertEquals(
            [
                'foo' => [
                    0 => 1,
                    1 => 2,
                    2 => 3,
                ],
                'bar' => [
                    0 => 1,
                    1 => 2,
                    2 => 3,
                ],
            ],
            $a->dump()
        );
        self::assertNotSame($a, $this->instance);
    }

    public function testDiff(): void
    {
        $a = $this->instance->diff([3, 4, 5, 6]);

        self::assertEquals(
            [1, 2],
            $a->dump()
        );
        self::assertNotSame($a, $this->instance);
    }

    public function testMerge(): void
    {
        $data1 = [
            'green' => 'Hulk',
            'red' => 'empty',
            'human' => [
                'dark' => 'empty',
                'black' => [
                    'male' => 'empty',
                    'female' => 'empty',
                    'no-gender' => 'empty',
                ],
            ],
        ];

        $data2 = [
            'ai' => 'Jarvis',
            'agent' => 'Phil Coulson',
            'red' => [
                'left' => 'Pepper',
                'right' => 'Iron Man',
            ],
            'human' => [
                'dark' => 'Nick Fury',
                'black' => [
                    'female' => 'Black Widow',
                    'male' => 'Loki',
                ],
            ],
        ];

        self::assertEquals(
            array_merge($data1, $data2),
            arr($data1)->merge(arr($data2))->dump()
        );
    }

    public function testFillWith(): void
    {
        $a = ArrayObject::fillWith(5, 3, 'Y');

        self::assertEquals(
            [
                5 => 'Y',
                6 => 'Y',
                7 => 'Y',
            ],
            $a->dump()
        );
    }

    public function testIntersectKey(): void
    {
        $a = $this->getAssoc();

        $b = arr(['flower' => 'Rose', 'animal' => 'bird']);

        self::assertEquals(['flower' => 'sakura'], $a->intersectKey($b)->dump());
    }

    public function testIntersect(): void
    {
        $a = $this->getAssoc();

        $b = arr(['flower2' => 'sakura', 'animal' => 'bird']);

        self::assertEquals(['flower' => 'sakura'], $a->intersect($b)->dump());
    }

    public function testRange(): void
    {
        $a = ArrayObject::range(1, 5);

        self::assertEquals(range(1, 5), $a->dump());
    }

    public function testFlip(): void
    {
        $a = $this->getAssoc();

        self::assertEquals(
            array_flip($a->dump()),
            $a->flip()->dump()
        );
    }

    public function testDiffKeys(): void
    {
        $a = $this->getAssoc();

        $b = arr(['flower' => 'Rose', 'animal' => 'bird']);

        self::assertEquals(['foo' => 'bar'], $a->diffKeys($b)->dump());
    }

    /**
     * @see  ArrayObject::countValues()
     */
    public function testCountValues(): void
    {
        $a = arr([1, 1, 2, 2, 2, 3, 3, 3, 3, 4, 5, 5]);

        self::assertEquals(
            [
                1 => 2,
                2 => 3,
                3 => 4,
                4 => 1,
                5 => 2,
            ],
            $a->countValues()->dump()
        );
    }

    /**
     * @see  ArrayObject::crossJoin()
     */
    public function testCrossJoin(): void
    {
        $a = arr([1, 2])->crossJoin(['a', 'b']);

        self::assertEquals(
            [
                [1, 'a'],
                [1, 'b'],
                [2, 'a'],
                [2, 'b'],
            ],
            $a->dump()
        );

        $b = arr([1, 2])->crossJoin(['a', 'b'], ['I', 'II']);

        self::assertEquals(
            [
                [1, 'a', 'I'],
                [1, 'a', 'II'],
                [1, 'b', 'I'],
                [1, 'b', 'II'],
                [2, 'a', 'I'],
                [2, 'a', 'II'],
                [2, 'b', 'I'],
                [2, 'b', 'II'],
            ],
            $b->dump()
        );
    }

    public function testRand(): void
    {
        $a = arr(['A', 'B', 'C', 'D', 'E']);

        $indexes = $a->rand(2);

        self::assertEquals($indexes->intersect($a->keys())->dump(), $indexes->dump());
    }

    protected function setUp(): void
    {
        $this->instance = arr([1, 2, 3]);
    }

    protected function tearDown(): void
    {
    }

    protected function getAssoc(): ArrayObject
    {
        return new ArrayObject(['foo' => 'bar', 'flower' => 'sakura']);
    }
}
