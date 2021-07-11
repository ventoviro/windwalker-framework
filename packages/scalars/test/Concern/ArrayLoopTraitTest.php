<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Scalars\Test\Concern;

use PHPUnit\Framework\TestCase;
use Windwalker\Scalars\ArrayObject;
use Windwalker\Scalars\StringObject;
use Windwalker\Utilities\Context\Loop;

use function Windwalker\arr;
use function Windwalker\where;

/**
 * The ArrayLoopTraitTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ArrayLoopTraitTest extends TestCase
{
    protected $instance;

    public function testPartition(): void
    {
        [$a1, $a2] = $this->instance->partition(
            function ($v) {
                return $v > 1;
            }
        );

        self::assertEquals([2, 3], $a1->dump());
        self::assertEquals([1], $a2->dump());
    }

    public function testFindFirst(): void
    {
        $a = ArrayObject::range(1, 10);

        $r = $a->findFirst(
            function ($v) {
                return $v >= 5;
            }
        );

        self::assertEquals(5, $r);
    }

    public function testFilter(): void
    {
        $a = ArrayObject::range(1, 10);

        $a = $a->filter(
            function ($v) {
                return $v % 2 === 1;
            }
        );

        self::assertEquals([1, 3, 5, 7, 9], $a->values()->dump());
        self::assertEquals([0, 2, 4, 6, 8], $a->keys()->dump());
    }

    public function testWalkRecursive(): void
    {
        $a = arr(
            $src = [
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
            ]
        );

        $callback = function (&$v, $k) {
            return $v = strtoupper($v);
        };
        $b = $a->walkRecursive($callback);
        array_walk_recursive($src, $callback);

        self::assertEquals($src, $b->dump());
    }

    public function testQuery(): void
    {
        $a = arr(
            $data = [
                [
                    'id' => 1,
                    'title' => 'Julius Caesar',
                    'data' => (object) ['foo' => 'bar'],
                ],
                [
                    'id' => 2,
                    'title' => 'Macbeth',
                    'data' => [],
                ],
                [
                    'id' => 3,
                    'title' => 'Othello',
                    'data' => 123,
                ],
                [
                    'id' => 4,
                    'title' => 'Hamlet',
                    'data' => true,
                ],
            ]
        );

        // Test id equals
        $this->assertEquals([$data[1]], $a->query(['id' => 2])->dump());

        // Test compare wrapper
        $this->assertEquals([$data[1]], $a->query([where('id', '=', 2)])->dump());
        $this->assertEquals(
            [$data[1]],
            $a->query(
                [
                    'id' => function ($v) {
                        return $v === 2;
                    },
                ]
            )->dump()
        );
    }

    public function testMap(): void
    {
        $a = $this->instance->map(
            function ($v) {
                return 1 + $v;
            }
        );

        self::assertEquals([2, 3, 4], $a->dump());
    }

    public function testWalk(): void
    {
        $a = $this->instance->walk(
            function (&$v) {
                return $v += 2;
            }
        );

        self::assertEquals([3, 4, 5], $a->dump());
    }

    public function testFind(): void
    {
        $a = arr(
            $data = [
                [
                    'id' => 1,
                    'title' => 'Julius Caesar',
                    'data' => (object) ['foo' => 'bar'],
                ],
                [
                    'id' => 2,
                    'title' => 'Macbeth',
                    'data' => [],
                ],
                [
                    'id' => 3,
                    'title' => 'Othello',
                    'data' => 123,
                ],
                [
                    'id' => 4,
                    'title' => 'Hamlet',
                    'data' => true,
                ],
            ]
        );

        self::assertEquals(
            $a->slice(0, 2)->dump(),
            $a->find(
                function ($item) {
                    return $item['id'] < 3;
                }
            )->dump()
        );
    }

    public function testReduce(): void
    {
        $a = $this->instance->reduce(
            function (int $sum, int $v) {
                return $sum + $v;
            },
            5
        )->toInteger();

        self::assertEquals(11, $a);

        $a = arr(
            $data = [
                [1, 2],
                [3, 4],
                [5, 6],
            ]
        )->reduce(
            function (array $sum, array $v) {
                return array_merge($sum, $v);
            },
            ['A', 'B']
        );

        self::assertEquals(array_merge(['A', 'B'], ...$data), $a->dump());
    }

    public function testMapWithKeys(): void
    {
        $a = $this->getAssoc()->mapWithKeys(
            function ($v, $k) {
                return [$v => $k];
            }
        );

        self::assertEquals(['bar' => 'foo', 'sakura' => 'flower'], $a->dump());

        $src = arr(
            [
                1 => 'a',
                2 => 'b',
                3 => 'b',
                4 => 'c',
                5 => 'a',
                6 => 'a',
            ]
        );

        $expected = [
            'a' => [1, 5, 6],
            'b' => [2, 3],
            'c' => 4,
        ];

        self::assertEquals(
            $expected,
            $src->mapWithKeys(
                function ($v, $k) {
                    return [$v => $k];
                },
                $src::GROUP_TYPE_MIX
            )->dump()
        );
    }

    public function testEach(): void
    {
        $r = [];
        $this->instance->each(
            function ($v, $k) use (&$r) {
                return $r[] = $k . '-' . $v;
            }
        );

        self::assertEquals(
            [
                '0-1',
                '1-2',
                '2-3',
            ],
            $r
        );

        $r = [];
        $this->instance->each(
            static function ($v, $k, Loop $loop) use (&$r) {
                if ($v > 1) {
                    return $loop->stop();
                }

                return $r[] = $k . '-' . $v;
            }
        );

        self::assertEquals(
            [
                '0-1',
            ],
            $r
        );

        $a = arr(
            [
                [1, 2, 3],
                [4, 5, 6],
                [7, 8, 9],
                [10, 11, 12],
            ]
        )->wrapAll();

        $r = [];

        $a->each(
            function (ArrayObject $v, $k, $l) use (&$r) {
                $v->each(
                    function ($v, $k, Loop $loop) use (&$r) {
                        if ($v === 4) {
                            $loop->stop(2);

                            return;
                        }

                        $r[] = $v;
                    }
                );
            }
        );

        self::assertEquals(
            [1, 2, 3],
            $r
        );
    }

    public function testMapAs(): void
    {
        $a = $this->instance->mapAs(StringObject::class);

        foreach ($a as $i => $v) {
            self::assertInstanceOf(
                StringObject::class,
                $v,
                sprintf('Index %s should be StringObject', $i)
            );
        }
    }

    public function testReject(): void
    {
        $a = $this->instance->reject(
            function ($v) {
                return $v > 1;
            }
        );

        self::assertEquals([1], $a->dump());
    }

    public function testMapRecursive(): void
    {
        $a = arr(
            $src = [
                'ai' => 'Jarvis',
                'agent' => 'Phil Coulson',
                'red' => [
                    'left' => 'Pepper',
                    'right' => 'Iron Man',
                ],
                'human' => [
                    'dark' => 'Nick Fury',
                    'black' => arr(
                        [
                            'female' => 'Black Widow',
                            'male' => 'Loki',
                        ]
                    ),
                ],
            ]
        )->mapRecursive(
            function ($v) {
                return is_string($v) ? strtoupper($v) : $v;
            },
            false,
            true
        );

        $expected = [
            'ai' => 'JARVIS',
            'agent' => 'PHIL COULSON',
            'red' => [
                'left' => 'PEPPER',
                'right' => 'IRON MAN',
            ],
            'human' => [
                'dark' => 'NICK FURY',
                'black' => [
                    'male' => 'LOKI',
                    'female' => 'BLACK WIDOW',
                ],
            ],
        ];

        self::assertEquals(
            $expected,
            $a->dump()
        );
    }

    public function testFlatMap(): void
    {
        $a = arr(
            [
                ['name' => 'Sally'],
                ['school' => 'Arkansas'],
                ['age' => 28],
            ]
        );

        $b = $a->flatMap(
            function ($values) {
                return array_map('strtoupper', $values);
            }
        );

        self::assertEquals(
            ['name' => 'SALLY', 'school' => 'ARKANSAS', 'age' => '28'],
            $b->dump()
        );
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
