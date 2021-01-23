<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Scalars\Test;

use BadMethodCallException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Windwalker\Scalars\StringObject;
use Windwalker\Utilities\Str;
use Windwalker\Utilities\Test\MbstringTestTrait;
use Windwalker\Utilities\Utf8String;

use function Windwalker\str;

/**
 * The StringObjectTest class.
 *
 * @since  {DEPLOY_VERSION}
 */
class StringObjectTest extends TestCase
{
    use MbstringTestTrait;

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    /**
     * testFunctionCreate
     *
     * @return  void
     */
    public function testFunctionCreate()
    {
        $s = str('白日依山盡', StringObject::ENCODING_US_ASCII);

        self::assertEquals('白日依山盡', $s->getString());
        self::assertEquals(StringObject::ENCODING_US_ASCII, $s->getEncoding());
    }

    /**
     * testStaticCreate
     *
     * @return  void
     */
    public function testStaticCreate()
    {
        $s = StringObject::create('白日依山盡', StringObject::ENCODING_US_ASCII);

        self::assertEquals('白日依山盡', $s->getString());
        self::assertEquals(StringObject::ENCODING_US_ASCII, $s->getEncoding());
    }

    /**
     * testStringAccess
     *
     * @return  void
     */
    public function testStringAccess()
    {
        $s = new StringObject('Foo');

        self::assertEquals('Foo', $s->getString());

        $s2 = $s->withString('白日依山盡');

        self::assertNotSame($s, $s2);
        self::assertNotEquals('白日依山盡', $s->getString());
        self::assertEquals('白日依山盡', $s2->getString());
    }

    /**
     * testEncodingAccess
     *
     * @return  void
     */
    public function testEncodingAccess()
    {
        $s = new StringObject();

        self::assertEquals('UTF-8', $s->getEncoding());

        $s2 = $s->withEncoding(StringObject::ENCODING_DEFAULT_ISO);

        self::assertNotSame($s, $s2);
        self::assertNotEquals(StringObject::ENCODING_DEFAULT_ISO, $s->getEncoding());
        self::assertEquals(StringObject::ENCODING_DEFAULT_ISO, $s2->getEncoding());
    }

    /**
     * testCallProxy
     *
     * @return  void
     */
    public function testCallProxy()
    {
        // Test return bool
        self::assertEquals(
            Str::endsWith('FooBar', 'bar', false, Str::ENCODING_UTF8),
            (new StringObject('FooBar', StringObject::ENCODING_UTF8))->endsWith('bar', false)
        );

        // Test return string
        self::assertEquals(
            Str::slice('白日依山盡', 1, 3, Str::ENCODING_UTF8),
            (new StringObject('白日依山盡', StringObject::ENCODING_UTF8))->slice(1, 3)
        );

        self::assertEquals(
            Str::slice('白日依山盡', 1, 3, Str::ENCODING_DEFAULT_ISO),
            (new StringObject('白日依山盡', StringObject::ENCODING_DEFAULT_ISO))->slice(1, 3)
        );

        $this->expectException(BadMethodCallException::class);

        (new StringObject('Foo'))->notexists();
    }

    /**
     * testOffsetGet
     *
     * @param  string  $expected
     * @param  int     $offset
     *
     * @dataProvider offsetGetProvider
     */
    public function testOffsetGet(int $offset, string $expected)
    {
        $s = new StringObject('白日依山盡');

        self::assertEquals($expected, (string) $s[$offset]);
    }

    /**
     * offsetGetProvider
     *
     * @return  array
     */
    public function offsetGetProvider(): array
    {
        return [
            [0, '白'],
            [3, '山'],
            [10, ''],
            [-1, '盡'],
            [-3, '依'],
            [-16, ''],
        ];
    }

    /**
     * testOffsetSet
     *
     * @param  string  $string
     * @param  string  $replace
     * @param  int     $offset
     * @param  string  $expected
     *
     * @return  void
     *
     * @dataProvider offsetSetProvider
     */
    public function testOffsetSet(string $string, string $replace, int $offset, string $expected)
    {
        $s = new StringObject($string);

        $s[$offset] = $replace;

        self::assertEquals($expected, (string) $s);
    }

    /**
     * offsetSetProvider
     *
     * @return  array
     */
    public function offsetSetProvider(): array
    {
        return [
            ['Foobar', ' B', 3, 'Foo Bar'],
            ['桃之夭夭', '逃', 0, '逃之夭夭'],
            ['下筆千言', '千萬', 2, '下筆千萬言'],
        ];
    }

    /**
     * testOffsetUnset
     *
     * @param  string  $string
     * @param  int     $offset
     * @param  string  $expected
     *
     * @return  void
     *
     * @dataProvider offsetUnsetProvider
     */
    public function testOffsetUnset(string $string, int $offset, string $expected)
    {
        $s = new StringObject($string);

        unset($s[$offset]);

        self::assertEquals($expected, (string) $s);
    }

    /**
     * offsetUnsetProvider
     *
     * @return  array
     */
    public function offsetUnsetProvider(): array
    {
        return [
            ['Foobar', 3, 'Fooar'],
            ['桃之夭夭', 0, '之夭夭'],
            ['下筆千言', 2, '下筆言'],
            ['白日依山盡', -2, '白日依盡'],
            ['白日依山盡', -5, '日依山盡'],
            ['白日依山盡', 5, '白日依山盡'],
            ['下筆千言', 6, '下筆千言'],
            ['下筆千言', -8, '下筆千言'],
        ];
    }

    /**
     * testOffsetExists
     *
     * @return  void
     */
    public function testOffsetExists()
    {
        $s = new StringObject('白日依山盡');

        self::assertTrue(isset($s[0]));
        self::assertTrue(isset($s[3]));
        self::assertTrue(isset($s[4]));
        self::assertFalse(isset($s[5]));
        self::assertTrue(isset($s[-5]));
        self::assertFalse(isset($s[9]));
        self::assertTrue(isset($s[-1]));
        self::assertTrue(isset($s[-3]));
        self::assertFalse(isset($s[-9]));
    }

    /**
     * testCount
     *
     * @return  void
     */
    public function testCount()
    {
        self::assertCount(6, new StringObject('Foobar'));
        self::assertCount(6, new StringObject('Foobar', StringObject::ENCODING_DEFAULT_ISO));
        self::assertCount(5, new StringObject('白日依山盡'));
        self::assertCount(15, new StringObject('白日依山盡', StringObject::ENCODING_DEFAULT_ISO));
    }

    /**
     * testGetIterator
     *
     * @return  void
     */
    public function testGetIterator()
    {
        $s = new StringObject('白日依山盡');

        self::assertEquals(Utf8String::strSplit((string) $s), iterator_to_array($s));
    }

    /**
     * testToLowerCase
     *
     * @param  string  $string
     * @param  string  $expected
     *
     * @return  void
     *
     * @dataProvider toLowerCaseProvider
     */
    public function testToLowerCase(string $string, string $expected)
    {
        $s  = new StringObject($string);
        $s2 = $s->toLowerCase();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals((string) $s2, $expected);
    }

    /**
     * toLowerCaseProvider
     *
     * @return  array
     */
    public function toLowerCaseProvider(): array
    {
        return [
            ['FooBar', 'foobar'],
            ['FÒÔBÀŘ', 'fòôbàř'],
            ['山巔一寺一壺酒', '山巔一寺一壺酒'],
        ];
    }

    /**
     * testToUpperCase
     *
     * @param  string  $string
     * @param  string  $expected
     *
     * @return  void
     *
     * @dataProvider toUpperCaseProvider
     */
    public function testToUpperCase(string $string, string $expected)
    {
        $s  = new StringObject($string);
        $s2 = $s->toUpperCase();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * toUpperCaseProvider
     *
     * @return  array
     */
    public function toUpperCaseProvider(): array
    {
        return [
            ['FooBar', 'FOOBAR'],
            ['FÒÔBÀŘ', 'FÒÔBÀŘ'],
            ['山巔一寺一壺酒', '山巔一寺一壺酒'],
        ];
    }

    /**
     * testLength
     *
     * @param  string       $string
     * @param  int          $expected
     * @param  string|null  $encoding
     *
     * @return  void
     *
     * @dataProvider lengthProvider
     */
    public function testLength(string $string, int $expected, string $encoding = null)
    {
        $s      = new StringObject($string, $encoding);
        $length = $s->length();

        self::assertEquals($expected, $length);
    }

    /**
     * lengthProvider
     *
     * @return  array
     */
    public function lengthProvider(): array
    {
        return [
            ['Foo Bar', 7],
            ['', 0],
            ['FÒÔ BÀŘ', 7],
            ['FÒÔ BÀŘ', 11, StringObject::ENCODING_DEFAULT_ISO],
            ['山巔一寺一壺酒', 7],
            ['山巔一寺一壺酒', 21, StringObject::ENCODING_DEFAULT_ISO],
            ['山巔一寺一壺酒', 21, StringObject::ENCODING_US_ASCII],
            ['山巔一寺一壺酒 二柳舞扇舞 把酒棄舊山 惡善百世流', 25],
            ['山巔一寺一壺酒 二柳舞扇舞 把酒棄舊山 惡善百世流', 69, StringObject::ENCODING_DEFAULT_ISO],
        ];
    }

    /**
     * testReplace
     *
     * @param  string  $string
     * @param          $search
     * @param          $replacement
     * @param          $expected
     * @param  int     $count
     *
     * @return  void
     *
     * @dataProvider replaceProvider
     */
    public function testReplace(string $string, $search, $replacement, $expected, int $count)
    {
        $s  = new StringObject($string);
        $s2 = $s->replace($search, $replacement, $c);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
        self::assertEquals($count, $c);
    }

    /**
     * replaceProvider
     *
     * @return  array
     */
    public function replaceProvider(): array
    {
        return [
            ['Foobar', 'oo', 'ii', 'Fiibar', 1],
            ['FlowerSakuraFlowerSakuraFlowerSakura', 'Sakura', 'Olive', 'FlowerOliveFlowerOliveFlowerOlive', 3],
            ['庭院深深深幾許', '深', '_', '庭院___幾許', 3],

            [
                'Foobar',
                ['o', 'r'],
                'i',
                'Fiibai',
                3,
            ],
            [
                'Foobar',
                ['o', 'r'],
                ['i', 'k'],
                'Fiibak',
                3,
            ],
        ];
    }

    /**
     * testChop
     *
     * @param  string  $string
     * @param  int     $length
     * @param  array   $expected
     *
     * @dataProvider strSplitProvider
     */
    public function testChop(string $string, int $length, $expected)
    {
        $s = new StringObject($string);

        try {
            self::assertEquals($expected, $s->chop($length)->dump());
        } catch (InvalidArgumentException $e) {
            self::assertEquals(0, $length);
        }
    }

    /**
     * testCompare
     *
     * @param  string  $str1
     * @param  string  $str2
     * @param  int     $expected
     *
     * @dataProvider strcmpProvider
     */
    public function testCompare(string $str1, string $str2, int $expected)
    {
        $s = new StringObject($str1);

        $actual = $s->compare($str2);

        if ($actual !== 0) {
            $actual /= abs($actual);
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * testCompare
     *
     * @param  string  $str1
     * @param  string  $str2
     * @param  int     $expected
     *
     * @dataProvider strcasecmpProvider
     */
    public function testCompareInsensitive(string $str1, string $str2, int $expected)
    {
        $s = new StringObject($str1);

        $actual = $s->compare($str2, false);

        if ($actual !== 0) {
            $actual /= abs($actual);
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * testReverse
     *
     * @param  string  $string
     * @param  string  $expected
     *
     * @dataProvider strrevProvider
     */
    public function testReverse(string $string, string $expected)
    {
        $s  = new StringObject($string);
        $s2 = $s->reverse();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testSubstrReplace
     *
     * @param  string  $expected
     * @param  mixed   $string
     * @param  mixed   $replace
     * @param  int     $start
     * @param  int     $offset
     *
     * @dataProvider substrReplaceProvider
     */
    public function testSubstrReplace(
        string $expected,
        $string,
        $replace,
        $start = null,
        $offset = null
    ) {
        if (is_array($string)) {
            self::markTestSkipped('StringObject only test string.');

            return;
        }

        $s  = new StringObject($string);
        $s2 = $s->substrReplace($replace, $start, $offset);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testLtrim
     *
     * @param  string       $string
     * @param  null|string  $charlist
     * @param  string       $expected
     *
     * @dataProvider ltrimProvider
     */
    public function testTrimLeft(string $string, ?string $charlist, string $expected)
    {
        $s  = new StringObject($string);
        $s2 = $s->trimLeft($charlist);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testRtrim
     *
     * @param  string       $string
     * @param  null|string  $charlist
     * @param  string       $expected
     *
     * @return  void
     *
     * @dataProvider rtrimProvider
     */
    public function testTrimRight(string $string, ?string $charlist, string $expected)
    {
        $s  = new StringObject($string);
        $s2 = $s->trimRight($charlist);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testTrim
     *
     * @param  string       $string
     * @param  null|string  $charlist
     * @param  string       $expected
     *
     * @return  void
     *
     * @dataProvider trimProvider
     */
    public function testTrim(string $string, ?string $charlist, string $expected)
    {
        $s  = new StringObject($string);
        $s2 = $s->trim($charlist);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testUcfirst
     *
     * @param  string  $string
     * @param  string  $expected
     *
     * @dataProvider ucfirstProvider
     */
    public function testUpperCaseFirst(string $string, string $expected)
    {
        $s  = new StringObject($string);
        $s2 = $s->upperCaseFirst();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testLcfirst
     *
     * @param  string  $string
     * @param  string  $expected
     *
     * @dataProvider lcfirstProvider
     */
    public function testLowerCaseFirst(string $string, string $expected)
    {
        $s  = new StringObject($string);
        $s2 = $s->lowerCaseFirst();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testUpperCaseWords
     *
     * @param  string  $string
     * @param  string  $expected
     *
     * @return  void
     *
     * @dataProvider ucwordsProvider
     */
    public function testUpperCaseWords(string $string, string $expected)
    {
        $s  = new StringObject($string);
        $s2 = $s->upperCaseWords();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testSubstrCount
     *
     * @param  string  $string
     * @param  string  $search
     * @param  int     $expected
     * @param  bool    $caseSensitive
     *
     * @dataProvider substrCountProvider
     */
    public function testSubstrCount(string $string, string $search, int $expected, bool $caseSensitive)
    {
        $s = new StringObject($string);

        self::assertEquals($expected, $s->substrCount($search, $caseSensitive));
    }

    /**
     * testIndexOf
     *
     * @param  string  $string
     * @param  string  $search
     * @param  int     $expected
     *
     * @return  void
     *
     * @dataProvider indexOfProvider
     */
    public function testIndexOf(string $string, string $search, $expected)
    {
        $s = new StringObject($string);

        self::assertSame($expected, $s->indexOf($search));
    }

    /**
     * indexOfProvider
     *
     * @return  array
     */
    public function indexOfProvider(): array
    {
        return [
            ['FooBar', 'B', 3],
            ['FooBar', 'asd', -1],
            ['山巔一寺一壺酒', '一寺', 2],
            ['山巔一寺一壺酒', '一', 2],
            ['山巔一寺一壺酒', '山', 0],
            ['山巔一寺一壺酒', '舞扇舞', -1],
        ];
    }

    /**
     * testIndexOf
     *
     * @param  string  $string
     * @param  string  $search
     * @param  int     $expected
     *
     * @return  void
     *
     * @dataProvider indexOfLastProvider
     */
    public function testIndexOfLast(string $string, string $search, $expected)
    {
        $s = new StringObject($string);

        self::assertSame($expected, $s->indexOfLast($search));
    }

    /**
     * indexOfProvider
     *
     * @return  array
     */
    public function indexOfLastProvider(): array
    {
        return [
            ['FooBarBaz', 'B', 6],
            ['FooBarBaz', 'asd', -1],
            ['山巔一寺一壺酒', '一寺', 2],
            ['山巔一寺一壺酒', '一', 4],
            ['山巔一寺一壺酒', '酒', 6],
            ['山巔一寺一壺酒', '舞扇舞', -1],
        ];
    }

    /**
     * testExplode
     *
     * @param  string  $string
     * @param  string  $delimiter
     * @param  array   $expected
     *
     * @return  void
     *
     * @dataProvider explodeProvider
     */
    public function testExplode(string $string, string $delimiter, array $expected)
    {
        $s = new StringObject($string);

        self::assertEquals($expected, $s->explode($delimiter)->dump());
    }

    /**
     * explodeProvider
     *
     * @return  array
     */
    public function explodeProvider(): array
    {
        return [
            ['Foo/Bar/Yoo', '/', ['Foo', 'Bar', 'Yoo']],
            ['山巔一寺一壺酒', '一', ['山巔', '寺', '壺酒']],
        ];
    }

    /**
     * testApply
     *
     * @return  void
     */
    public function testApply()
    {
        $s = new StringObject('FooBar');

        $s2 = $s->apply(
            function ($string) {
                return strtoupper($string);
            }
        );

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals('FOOBAR', (string) $s2);
    }

    public function testPipe()
    {
        $s = new StringObject('FooBar');

        $s2 = $s->pipe(
            static function (StringObject $str) {
                return $str->toUpperCase();
            }
        );

        self::assertNotSame($s, $s2);
        self::assertEquals('FOOBAR', (string) $s2);
    }

    public function testAppend(): void
    {
        $str  = str('foo');
        $str2 = $str->append(' bar');

        self::assertEquals('foo bar', (string) $str2);
        self::assertNotSame($str, $str2);
    }

    public function testPrepend(): void
    {
        $str  = str('foo');
        $str2 = $str->prepend('bar ');

        self::assertEquals('bar foo', (string) $str2);
        self::assertNotSame($str, $str2);
    }
}
