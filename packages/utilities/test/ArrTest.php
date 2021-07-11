<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use ArrayAccess;
use ArrayObject;
use EmptyIterator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Utilities\Arr;

use function Windwalker\where;

/**
 * The ArrTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ArrTest extends TestCase
{
    use BaseAssertionTrait;

    /**
     * testDef
     *
     * @param  array|object  $array
     * @param  string        $key
     * @param  string        $value
     * @param  mixed         $expected
     *
     * @return  void
     *
     * @dataProvider providerTestDef
     */
    public function testDef($array, $key, $value, $expected): void
    {
        self::assertEquals($expected, $return = Arr::def($array, $key, $value));

        if (is_object($array)) {
            self::assertSame($array, $return);
            self::assertEquals($array, $expected);
        }
    }

    /**
     * providerTestDef
     *
     * @return  array
     */
    public function providerTestDef(): array
    {
        return [
            [
                ['foo' => 'bar'],
                'foo',
                'yoo',
                ['foo' => 'bar'],
            ],
            [
                ['foo' => 'bar'],
                'baz',
                'goo',
                ['foo' => 'bar', 'baz' => 'goo'],
            ],
            [
                (object) ['foo' => 'bar'],
                'foo',
                'yoo',
                (object) ['foo' => 'bar'],
            ],
            [
                (object) ['foo' => 'bar'],
                'baz',
                'goo',
                (object) ['foo' => 'bar', 'baz' => 'goo'],
            ],
        ];
    }

    /**
     * testHas
     *
     * @return  void
     */
    public function testHas()
    {
        self::assertTrue(Arr::has(['foo' => 'bar'], 'foo'));
        self::assertFalse(Arr::has(['foo' => 'bar'], 'yoo'));
        self::assertTrue(Arr::has(['foo' => ['bar' => 'yoo']], 'foo.bar'));
        self::assertFalse(Arr::has(['foo' => ['bar' => 'yoo']], ''));
        self::assertTrue(Arr::has(['foo' => new ArrayObject(['bar' => 'yoo'])], 'foo.bar'));
        self::assertTrue(Arr::has(['foo' => ['bar' => 'yoo']], 'foo/bar', '/'));
        self::assertTrue(Arr::has(['foo' => ['bar' => 'yoo']], 'foo'));
    }

    /**
     * testCollapse
     *
     * @return  void
     */
    public function testCollapse(): void
    {
        $src = [
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

        $exp = [
            'ai' => 'Jarvis',
            'agent' => 'Phil Coulson',
            'green' => 'Hulk',
            'left' => 'Pepper',
            'right' => 'Iron Man',
            'dark' => 'Nick Fury',
            'male' => 'Loki',
            'female' => 'Black Widow',
            'no-gender' => 'empty',
        ];

        self::assertEquals($exp, Arr::collapse($src, true));

        $exp = [
            'Jarvis',
            'Phil Coulson',
            'Hulk',
            'Pepper',
            'Iron Man',
            'Nick Fury',
            'Loki',
            'Black Widow',
            'empty',
        ];

        self::assertEquals($exp, Arr::collapse($src));
    }

    /**
     * testFlatten
     *
     * @return  void
     */
    public function testFlatten(): void
    {
        $array = [
            'flower' => 'sakura',
            'olive' => 'peace',
            'pos1' => [
                'sunflower' => 'love',
            ],
            'pos2' => [
                'cornflower' => 'elegant',
                'pos3' => [
                    'olive',
                ],
            ],
        ];

        $flatted = Arr::flatten($array);

        $this->assertEquals($flatted['pos1.sunflower'], 'love');

        $flatted = Arr::flatten($array, '/');

        $this->assertEquals($flatted['pos1/sunflower'], 'love');

        // Test depth
        $flatted = Arr::flatten($array, '/', 0);

        $this->assertEquals($flatted['pos2/pos3/0'], 'olive');

        $flatted = Arr::flatten($array, '/', 1);

        $this->assertEquals($flatted['pos2']['pos3'], ['olive']);

        $flatted = Arr::flatten($array, '/', 2);

        $this->assertEquals($flatted['pos2/pos3'], ['olive']);

        $flatted = Arr::flatten($array, '/', 3);

        $this->assertEquals($flatted['pos2/pos3/0'], 'olive');

        $array = new ArrayObject(
            [
                'Apple' => [
                    ['name' => 'iPhone 6S', 'brand' => 'Apple'],
                ],
                'Samsung' => [
                    ['name' => 'Galaxy S7', 'brand' => 'Samsung'],
                ],
            ]
        );

        $expected = [
            'Apple.0' => ['name' => 'iPhone 6S', 'brand' => 'Apple'],
            'Samsung.0' => ['name' => 'Galaxy S7', 'brand' => 'Samsung'],
        ];

        $this->assertEquals($expected, Arr::flatten($array, '.', 2));
    }

    /**
     * testGet
     *
     * @return  void
     */
    public function testGet()
    {
        $data = [
            'flower' => 'sakura',
            'olive' => 'peace',
            'pos1' => [
                'sunflower' => 'love',
            ],
            'pos2' => [
                'cornflower' => 'elegant',
            ],
            'array' => [
                'A',
                'B',
                'C',
            ],
        ];

        $this->assertEquals('sakura', Arr::get($data, 'flower'));
        $this->assertEquals('love', Arr::get($data, 'pos1.sunflower'));
        $this->assertEquals('default', Arr::get($data, 'pos1.notexists') ?? 'default');
        $this->assertEquals('love', Arr::get($data, 'pos1/sunflower', '/'));
        $this->assertEquals($data['array'], Arr::get($data, 'array'));

        // Test reference
        $v = &Arr::get($data, 'pos1.sunflower');

        Arr::set($data, 'pos1.sunflower', 'new love');

        self::assertEquals('new love', $v, 'Reference not work');

        $data = (object) [
            'flower' => 'sakura',
            'olive' => 'peace',
            'pos1' => (object) [
                'sunflower' => 'love',
            ],
            'pos2' => new ArrayObject(
                [
                    'cornflower' => 'elegant',
                ]
            ),
            'array' => (object) [
                'A',
                'B',
                'C',
            ],
        ];

        $this->assertEquals('sakura', Arr::get($data, 'flower'));
        $this->assertEquals('love', Arr::get($data, 'pos1.sunflower'));
        $this->assertEquals('default', Arr::get($data, 'pos1.notexists') ?? 'default');
        $this->assertEquals('elegant', Arr::get($data, 'pos2.cornflower'));
        $this->assertEquals('love', Arr::get($data, 'pos1/sunflower', '/'));
        $this->assertEquals($data->array, Arr::get($data, 'array'));
        $this->assertNull(Arr::get($data, 'not.exists'));
    }

    /**
     * testSet
     *
     * @return  void
     */
    public function testSet()
    {
        $data = [];

        // One level
        $return = Arr::set($data, 'flower', 'sakura');

        $this->assertEquals('sakura', $return['flower']);

        // Multi-level
        $return = Arr::set($data, 'foo.bar', 'test');

        $this->assertEquals('test', $return['foo']['bar']);

        // Separator
        $return = Arr::set($data, 'foo/bar', 'play', '/');

        $this->assertEquals('play', $return['foo']['bar']);

        // Type
        $return = Arr::set($data, 'cloud/fly', 'bird', '/', 'stdClass');

        $this->assertEquals('bird', $return['cloud']->fly);

        // False
        Arr::set($data, '', 'goo');

        // Fix path
        $return = Arr::set($data, 'double..separators', 'value');

        $this->assertEquals('value', $return['double']['separators']);

        $this->assertExpectedException(
            function () use ($data) {
                Arr::set($data, 'a.b', 'c', '.', 'Non\Exists\Class');
            },
            InvalidArgumentException::class,
            'Type or class: Non\Exists\Class not exists'
        );
    }

    /**
     * testRemove
     *
     * @param  array|object  $array
     * @param  array|object  $expected
     * @param  int|string    $offset
     * @param  string        $separator
     *
     * @dataProvider providerTestRemove
     */
    public function testRemove($array, $expected, $offset, $separator)
    {
        $actual = Arr::remove($array, (string) $offset, $separator);

        self::assertEquals($expected, $actual);

        if (is_object($array)) {
            self::assertSame($array, $actual);
            self::assertIsObject($actual);
        }
    }

    /**
     * providerTestRemove
     *
     * @return  array
     */
    public function providerTestRemove(): array
    {
        return [
            [
                [1, 2, 3],
                [0 => 1, 2 => 3],
                1,
                '.',
            ],
            [
                [1, 2, 3],
                [1, 2, 3],
                5,
                '.',
            ],
            [
                [1, 2, 3],
                [1, 2, 3],
                '',
                '.',
            ],
            [
                ['foo' => 'bar', 'baz' => 'yoo'],
                ['baz' => 'yoo'],
                'foo',
                '.',
            ],
            [
                ['foo' => 'bar', 'baz' => 'yoo'],
                ['foo' => 'bar', 'baz' => 'yoo'],
                'haa',
                '.',
            ],
            [
                ['foo' => 'bar', 'baz' => ['joo' => 'hoo']],
                ['foo' => 'bar', 'baz' => []],
                'baz.joo',
                '.',
            ],
            [
                (object) ['foo' => 'bar', 'baz' => 'yoo'],
                (object) ['baz' => 'yoo'],
                'foo',
                '.',
            ],
            [
                (object) ['foo' => 'bar', 'baz' => 'yoo'],
                (object) ['foo' => 'bar', 'baz' => 'yoo'],
                'haa',
                '.',
            ],
            [
                (object) ['foo' => 'bar', 'baz' => ['joo' => 'hoo']],
                (object) ['foo' => 'bar', 'baz' => []],
                'baz/joo',
                '/',
            ],
        ];
    }

    /**
     * testKeep
     *
     * @return  void
     */
    public function testOnly()
    {
        $array = [
            'Lycoris' => 'energetic',
            'Sunflower' => 'worship',
            'Zinnia' => 'robust',
            'Lily' => 'love',
        ];

        self::assertEquals(
            ['Lycoris' => 'energetic', 'Zinnia' => 'robust'],
            Arr::only($array, ['Lycoris', 'Zinnia'])
        );

        self::assertEquals(
            ['Lycoris' => 'energetic'],
            Arr::only($array, ['Lycoris'])
        );

        self::assertEquals(
            (object) ['Lycoris' => 'energetic', 'Zinnia' => 'robust'],
            Arr::only((object) $array, ['Lycoris', 'Zinnia'])
        );

        self::assertEquals(
            (object) ['Lycoris' => 'energetic'],
            Arr::only((object) $array, ['Lycoris'])
        );
    }

    public function testExcept(): void
    {
        $array = [
            'Lycoris' => 'energetic',
            'Sunflower' => 'worship',
            'Zinnia' => 'robust',
            'Lily' => 'love',
        ];

        self::assertEquals(
            ['Lycoris' => 'energetic', 'Zinnia' => 'robust'],
            Arr::except($array, ['Sunflower', 'Lily'])
        );

        self::assertEquals(
            [
                'Sunflower' => 'worship',
                'Zinnia' => 'robust',
                'Lily' => 'love',
            ],
            Arr::except($array, ['Lycoris'])
        );

        self::assertEquals(
            (object) ['Lycoris' => 'energetic', 'Zinnia' => 'robust'],
            Arr::except((object) $array, ['Sunflower', 'Lily'])
        );

        self::assertEquals(
            (object) [
                'Sunflower' => 'worship',
                'Zinnia' => 'robust',
                'Lily' => 'love',
            ],
            Arr::except((object) $array, ['Lycoris'])
        );
    }

    /**
     * testFind
     *
     * @return  void
     */
    public function testFind()
    {
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
        ];

        $results = Arr::find(
            $data,
            function ($value, $key) {
                return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
            }
        );

        $this->assertEquals([$data[0], $data[3]], $results);

        // Keep key
        $results = Arr::find(
            $data,
            function ($value, &$key) {
                $key++;

                return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
            },
            true
        );

        $this->assertEquals([1 => $data[0], 4 => $data[3]], $results);

        // Offset limit
        $results = Arr::find(
            $data,
            function ($value, &$key) {
                $key++;

                return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
            },
            false,
            0,
            1
        );

        $this->assertEquals([$data[0]], $results);

        // Offset limit
        $results = Arr::find(
            $data,
            function ($value, &$key) {
                $key++;

                return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
            },
            false,
            1,
            1
        );

        $this->assertEquals([$data[3]], $results);

        // Test global function
        self::assertEquals(['foo' => 'bar'], Arr::find(['foo' => 'bar', 'baz' => ''], 'strlen', true));
    }

    /**
     * testFindFirst
     *
     * @return  void
     */
    public function testFindFirst()
    {
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
        ];

        $result = Arr::findFirst(
            $data,
            function ($value, $key) {
                return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
            }
        );

        $this->assertEquals($data[0], $result);

        $result = Arr::findFirst(
            $data,
            function ($value, $key) {
                return $value['title'] === 'No exists';
            }
        );

        $this->assertNull($result);
    }

    /**
     * testReject
     *
     * @return  void
     */
    public function testReject()
    {
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
        ];

        $results = Arr::reject(
            $data,
            function ($value, $key) {
                return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
            }
        );

        $this->assertEquals([$data[1], $data[2]], $results);
    }

    /**
     * testTakeout
     *
     * @return  void
     */
    public function testTakeout()
    {
        $array = [
            'one' => 1,
            'two' => 2,
            'three' => 3,
        ];

        self::assertEquals(2, Arr::takeout($array, 'two'));
        self::assertEquals(['one' => 1, 'three' => 3], $array);

        $array = [
            'one' => 1,
            'two' => ['two' => 2, 'three' => 3],
        ];

        self::assertEquals(2, Arr::takeout($array, 'two.two'));
        self::assertEquals(['one' => 1, 'two' => ['three' => 3]], $array);

        $array = [
            'one' => 1,
            'two' => 2,
            'three' => 3,
        ];

        self::assertEquals('default', Arr::takeout($array, 'foo', 'default'));
        self::assertEquals(['one' => 1, 'two' => 2, 'three' => 3], $array);

        $array = (object) [
            'one' => 1,
            'two' => 2,
            'three' => 3,
        ];

        self::assertEquals(2, Arr::takeout($array, 'two'));
    }

    /**
     * testSort
     *
     * @param $data
     * @param $expected
     * @param $condition
     * @param $descending
     *
     * @return  void
     *
     * @dataProvider providerTestSort
     */
    public function testSort($data, $expected, $condition, $descending)
    {
        $return = Arr::sort($data, $condition, $descending);

        self::assertEquals($expected, $return);
    }

    /**
     * providerTestSort
     *
     * @return  array
     */
    public function providerTestSort(): array
    {
        return [
            'simple array' => [
                [
                    ['name' => 'Car', 'price' => 200],
                    ['name' => 'Bike', 'price' => 100],
                    ['name' => 'Motor', 'price' => 150],
                ],
                [
                    1 => ['name' => 'Bike', 'price' => 100],
                    2 => ['name' => 'Motor', 'price' => 150],
                    0 => ['name' => 'Car', 'price' => 200],
                ],
                'price',
                false,
            ],
            'simple array use callback' => [
                [
                    2 => ['name' => 'Car', 'price' => 200],
                    3 => ['name' => 'Bike', 'price' => 100],
                    4 => ['name' => 'Motor', 'price' => 150],
                ],
                [
                    2 => ['name' => 'Car', 'price' => 200],
                    4 => ['name' => 'Motor', 'price' => 150],
                    3 => ['name' => 'Bike', 'price' => 100],
                ],
                function ($item, $key) {
                    return $item['price'];
                },
                true,
            ],
            'simple objects' => [
                [
                    (object) ['name' => 'Car', 'price' => 200],
                    (object) ['name' => 'Bike', 'price' => 100],
                    (object) ['name' => 'Motor', 'price' => 150],
                ],
                [
                    1 => (object) ['name' => 'Bike', 'price' => 100],
                    2 => (object) ['name' => 'Motor', 'price' => 150],
                    0 => (object) ['name' => 'Car', 'price' => 200],
                ],
                'price',
                false,
            ],
            'simple objects use callback' => [
                [
                    (object) ['name' => 'Bike', 'price' => 100],
                    (object) ['name' => 'Car', 'price' => 200],
                    (object) ['name' => 'Motor', 'price' => 150],
                ],
                [
                    1 => (object) ['name' => 'Car', 'price' => 200],
                    0 => (object) ['name' => 'Bike', 'price' => 100],
                    2 => (object) ['name' => 'Motor', 'price' => 150],
                ],
                function ($item, $key) {
                    return strlen($item->name);
                },
                false,
            ],
        ];
    }

    /**
     * testInvert
     *
     * @param $data
     * @param $expected
     *
     * @return  void
     *
     * @dataProvider providerTestInvert
     */
    public function testInvert($data, $expected)
    {
        self::assertEquals($expected, Arr::invert($data));
    }

    /**
     * providerTestInvert
     *
     * @return  array
     */
    public function providerTestInvert(): array
    {
        return [
            'Case 1' => [
                [
                    'Sakura' => ['1000', '1500', '1750'],
                    'Olive' => ['3000', '4000', '5000', '6000'],
                ],
                [
                    '1000' => 'Sakura',
                    '1500' => 'Sakura',
                    '1750' => 'Sakura',
                    '3000' => 'Olive',
                    '4000' => 'Olive',
                    '5000' => 'Olive',
                    '6000' => 'Olive',
                ],
            ],
            'Case 2' => [
                [
                    'Sakura' => [1000, 1500, 1750],
                    'Olive' => [2750, 3000, 4000, 5000, 6000],
                    'Sunflower' => [2000, 2500],
                    'Unspecified' => [],
                ],
                [
                    '1000' => 'Sakura',
                    '1500' => 'Sakura',
                    '1750' => 'Sakura',
                    '2750' => 'Olive',
                    '3000' => 'Olive',
                    '4000' => 'Olive',
                    '5000' => 'Olive',
                    '6000' => 'Olive',
                    '2000' => 'Sunflower',
                    '2500' => 'Sunflower',
                ],
            ],
            'Case 3' => [
                [
                    'Sakura' => [1000, 1500, 1750],
                    'valueNotAnArray' => 2750,
                    'withNonScalarValue' => [2000, [1000, 3000]],
                ],
                [
                    '1000' => 'Sakura',
                    '1500' => 'Sakura',
                    '1750' => 'Sakura',
                    '2000' => 'withNonScalarValue',
                ],
            ],
        ];
    }

    /**
     * testIsAssociative
     *
     * @return  void
     */
    public function testIsAssociative()
    {
        self::assertTrue(Arr::isAssociative(['foo' => 'bar', 'baz']));
        self::assertFalse(Arr::isAssociative(['bar', 'baz']));
        self::assertTrue(Arr::isAssociative([2 => 'bar', 1 => 'baz']));
    }

    /**
     * testAccessible
     *
     * @return  void
     */
    public function testAccessible()
    {
        self::assertTrue(Arr::isAccessible([]));
        self::assertTrue(Arr::isAccessible(new ArrayObject()));

        $array = new class implements ArrayAccess {
            public function offsetExists($offset)
            {
            }

            public function offsetGet($offset)
            {
            }

            public function offsetSet($offset, $value)
            {
            }

            public function offsetUnset($offset)
            {
            }
        };

        self::assertTrue(Arr::isAccessible($array));
        self::assertFalse(Arr::isAccessible(new EmptyIterator()));
        self::assertFalse(Arr::isAccessible(new stdClass()));
    }

    /**
     * testUnique
     *
     * @return  void
     */
    public function testUnique()
    {
        $array = [
            [1, 2, 3, [4]],
            [2, 2, 3, [4]],
            [3, 2, 3, [4]],
            [2, 2, 3, [4]],
            [3, 2, 3, [4]],
        ];

        $expected = [
            [1, 2, 3, [4]],
            [2, 2, 3, [4]],
            [3, 2, 3, [4]],
        ];

        self::assertEquals($expected, Arr::unique($array));
    }

    /**
     * testMerge
     *
     * @return  void
     */
    public function testMergeRecursive()
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

        $data3 = [
            'ai' => 'Ultron',
        ];

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

        $this->assertEquals($expected, Arr::mergeRecursive($data1, $data2));

        $expected['ai'] = 'Ultron';

        $this->assertEquals($expected, Arr::mergeRecursive($data1, $data2, $data3));

        $this->expectException(InvalidArgumentException::class);

        Arr::mergeRecursive('', 123);
    }

    /**
     * testDump
     *
     * @return  void
     */
    public function testDump()
    {
        $data = [
            1,
            2,
            'foo' => 'bar',
            (object) ['baz' => 'yoo'],
            new ArrayObject(['flower' => 'sakura', 'fruit' => 'apple']),
            ['max' => ['level' => ['test' => ['no' => 'show']]]],
            new class {
                protected $foo = 'bar';

                private $baz = 'yoo';

                public $flower = 'sakura';

                public static $car = 'toyota';
            },
        ];

        if (PHP_VERSION_ID < 70400) {
            $expected = <<<OUT
Array
(
    [0] => 1
    [1] => 2
    [foo] => bar
    [2] => stdClass Object
        (
            [baz] => yoo
        )

    [3] => ArrayObject Object
        (
            [flower] => sakura
            [fruit] => apple
        )

    [4] => Array
        (
            [max] => Array
                (
                    [level] => Array
                        *MAX LEVEL*

                )

        )

    [5] => class@anonymous Object
        (
            [foo:protected] => bar
            [baz:class@anonymous:private] => yoo
            [flower] => sakura
            [car:static] => toyota
        )

)
OUT;
        } else {
            $expected = <<<SHOW
Array
(
    [0] => 1
    [1] => 2
    [foo] => bar
    [2] => stdClass Object
        (
            [baz] => yoo
        )

    [3] => ArrayObject Object
        (
        )

    [4] => Array
        (
            [max] => Array
                (
                    [level] => Array
                        *MAX LEVEL*

                )

        )

    [5] => class@anonymous Object
        (
            [foo:protected] => bar
            [baz:class@anonymous:private] => yoo
            [flower] => sakura
            [car:static] => toyota
        )

)
SHOW;
        }

        self::assertStringSafeEquals($expected, Arr::dump($data, 4));
    }

    public static function testWhere(): void
    {
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
        ];

        self::assertEquals([$data[1]], Arr::where($data, 'title', '=', 'Macbeth'));
    }

    public function testQuery(): void
    {
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
        ];

        // Test id equals
        $this->assertEquals([$data[1]], Arr::query($data, ['id' => 2]));

        // Test compare wrapper
        $this->assertEquals([$data[1]], Arr::query($data, [where('id', '=', 2)]));
        $this->assertEquals(
            [$data[1]],
            Arr::query(
                $data,
                [
                    'id' => function ($v) {
                        return $v === 2;
                    },
                ]
            )
        );

        // Test strict equals
        $this->assertEquals([$data[0], $data[2], $data[3]], Arr::query($data, ['data' => true], false));
        $this->assertEquals([$data[3]], Arr::query($data, ['data' => true], true));

        // Test id GT
        $this->assertEquals([$data[1], $data[2], $data[3]], Arr::query($data, ['id >' => 1]));

        // Test id GTE
        $this->assertEquals([$data[1], $data[2], $data[3]], Arr::query($data, ['id >=' => 2]));

        // Test id LT
        $this->assertEquals([$data[0], $data[1]], Arr::query($data, ['id <' => 3]));

        // Test id LTE
        $this->assertEquals([$data[0], $data[1]], Arr::query($data, ['id <=' => 2]));

        // Test in array
        $this->assertEquals([$data[0], $data[2]], Arr::query($data, ['id' => [1, 3]]));

        // Test array equals
        $this->assertEquals([$data[0]], Arr::query($data, ['id' => 1, 'title' => 'Julius Caesar']));

        // Test object equals
        $object = new stdClass();
        $object->foo = 'bar';
        $this->assertEquals([$data[0], $data[3]], Arr::query($data, ['data' => $object]));

        // Test object strict equals
        $this->assertEquals([$data[0]], Arr::query($data, ['data' => $data[0]['data']], true));

        // Test Keep Index
        $this->assertEquals(
            [1 => $data[1], 2 => $data[2], 3 => $data[3]],
            Arr::query($data, ['id >=' => 2], false, true)
        );
    }

    public function testQueryWithCallback(): void
    {
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
        ];

        $results = Arr::query(
            $data,
            static function ($value, $key) {
                return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
            }
        );

        $this->assertEquals([$data[0], $data[3]], $results);
    }

    public function testMatch(): void
    {
        $data = [
            'id' => 1,
            'title' => 'Julius Caesar',
            'data' => (object) ['foo' => 'bar'],
        ];

        $this->assertTrue(Arr::match($data, ['id' => 1]));
        $this->assertTrue(Arr::match($data, ['id' => [1, 2, 3]]));
        $this->assertTrue(Arr::match($data, ['id' => 1, 'title' => 'Julius Caesar']));
        $this->assertFalse(Arr::match($data, ['id' => 5]));
        $this->assertFalse(Arr::match($data, ['id' => 1, 'title' => 'Hamlet']));
    }

    public function testFilterRecursive(): void
    {
        $src = [
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

        $expected = [
            'ai' => 'Jarvis',
            'red' =>
                [
                    'right' => 'Iron Man',
                ],
            'human' =>
                [
                    'black' =>
                        [
                            'female' => 'Black Widow',
                        ],
                ],
        ];

        self::assertEquals(
            $expected,
            Arr::filterRecursive(
                $src,
                function ($v) {
                    return strpos($v, 'a') !== false;
                }
            )
        );
    }

    public function testMapRecursive(): void
    {
        $src = [
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

        $expected = [
            'ai' => 'JARVIS',
            'agent' => 'PHIL COULSON',
            'green' => 'HULK',
            'red' => [
                'left' => 'PEPPER',
                'right' => 'IRON MAN',
            ],
            'human' => [
                'dark' => 'NICK FURY',
                'black' => [
                    'male' => 'LOKI',
                    'female' => 'BLACK WIDOW',
                    'no-gender' => 'EMPTY',
                ],
            ],
        ];

        self::assertEquals($expected, Arr::mapRecursive($src, 'strtoupper'));

        $src['human']['black'] = new ArrayObject($src['human']['black']);

        $expected = [
            'ai' => 'Jarvis-ai',
            'agent' => 'Phil Coulson-agent',
            'green' => 'Hulk-green',
            'red' => [
                'left' => 'Pepper-left',
                'right' => 'Iron Man-right',
            ],
            'human' => [
                'dark' => 'Nick Fury-dark',
                'black' => [
                    'male' => 'Loki-male',
                    'female' => 'Black Widow-female',
                    'no-gender' => 'empty-no-gender',
                ],
            ],
        ];

        self::assertEquals(
            $expected,
            Arr::mapRecursive(
                $src,
                function ($v, $k) {
                    return $v . '-' . $k;
                },
                true,
                true
            )
        );
    }

    public function testFlatMap(): void
    {
        $a = [
            ['name' => 'Sally'],
            ['school' => 'Arkansas'],
            ['age' => 28],
        ];

        $b = Arr::flatMap(
            $a,
            function ($values) {
                return array_map('strtoupper', $values);
            }
        );

        self::assertEquals(
            ['name' => 'SALLY', 'school' => 'ARKANSAS', 'age' => '28'],
            $b
        );
    }
}
