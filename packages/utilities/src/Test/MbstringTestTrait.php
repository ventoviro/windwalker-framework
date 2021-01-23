<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use Windwalker\Utilities\Utf8String;

/**
 * Trait MbstringTestTrait
 *
 * @since  __DEPLOY_VERSION__
 */
trait MbstringTestTrait
{
    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function isAsciiProvider(): array
    {
        return [
            ['ascii', true],
            ['1024', true],
            ['#$#@$%', true],
            ['áÑ', false],
            ['ÿ©', false],
            ['¡¾', false],
            ['÷™', false],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strposProvider(): array
    {
        return [
            [3, 'missing', 'sing', 0],
            [false, 'missing', 'sting', 0],
            [4, 'missing', 'ing', 0],
            [10, ' объектов на карте с', 'на карте', 0],
            [0, 'на карте с', 'на карте', 0, 0],
            [false, 'на карте с', 'на каррте', 0],
            [false, 'на карте с', 'на карте', 2],
            [3, 'missing', 'sing', 0],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strrposProvider(): array
    {
        return [
            [3, 'missing', 'sing', 0],
            [false, 'missing', 'sting', 0],
            [4, 'missing', 'ing', 0],
            [10, ' объектов на карте с', 'на карте', 0],
            [0, 'на карте с', 'на карте', 0],
            [false, 'на карте с', 'на каррте', 0],
            [3, 'на карте с', 'карт', 2],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function substrProvider(): array
    {
        return [
            ['issauga', 'Mississauga', 4, null],
            ['на карте с', ' объектов на карте с', 10, null],
            ['на ка', ' объектов на карте с', 10, 5],
            ['те с', ' объектов на карте с', -4, null],
            [false, ' объектов на карте с', 99, null],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strtolowerProvider(): array
    {
        return [
            ['Windwalker! Rocks', 'windwalker! rocks'],
            ['FÒÔbàř', 'fòôbàř'],
            ['fòôbàř', 'fòôbàř'],
            ['白日依山盡', '白日依山盡'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strtoupperProvider(): array
    {
        return [
            ['Windwalker! Rocks', 'WINDWALKER! ROCKS'],
            ['FÒÔbàř', 'FÒÔBÀŘ'],
            ['FÒÔBÀŘ', 'FÒÔBÀŘ'],
            ['白日依山盡', '白日依山盡'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strlenProvider(): array
    {
        return [
            ['Windwalker! Rocks', 17],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strIreplaceProvider(): array
    {
        return [
            ['Pig', 'cow', 'the pig jumped', null, 'the cow jumped'],
            ['Pig', 'cow', 'the pig jumped', 1, 'the cow jumped'],
            ['Pig', 'cow', 'the pig jumped over the cow', 1, 'the cow jumped over the cow'],
            [
                ['PIG', 'JUMPED'],
                ['cow', 'hopped'],
                'the pig jumped over the pig',
                null,
                'the cow hopped over the cow',
            ],
            ['шил', 'биш', 'Би шил идэй чадна', 1, 'Би биш идэй чадна'],
            ['/', ':', '/test/slashes/', null, ':test:slashes:'],
            ['/', ':', '/test/slashes/', 1, ':test/slashes/'],
            ['', ':', '/test/slashes/', null, '/test/slashes/'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strSplitProvider(): array
    {
        return [
            ['string', 1, ['s', 't', 'r', 'i', 'n', 'g']],
            ['string', 2, ['st', 'ri', 'ng']],
            ['волн', 3, ['вол', 'н']],
            ['волн', 1, ['в', 'о', 'л', 'н']],
            ['волн', 0, false],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strcasecmpProvider(): array
    {
        return [
            ['THIS IS STRING1', 'this is string1', 0],
            ['this is string1', 'this is string2', -1],
            ['this is string2', 'this is string1', 1],
            ['бгдпт', 'бгдпт', 0],
            ['àbc', 'abc', 1],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strcmpProvider(): array
    {
        return [
            ['THIS IS STRING1', 'this is string1', -1],
            ['this is string1', 'this is string2', -1],
            ['this is string2', 'this is string1', 1],
            ['a', 'B', 1],
            ['A', 'b', -1],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strcspnProvider(): array
    {
        return [
            ['subject <a> string <a>', '<>', 0, null, 8],
            ['Би шил {123} идэй {456} чадна', '}{', 0, null, 7],
            ['Би шил {123} идэй {456} чадна', '}{', 13, 10, 5],
            ['Би шил {123} идэй {456} чадна', '', 13, 10, 0],
            ['Not contains', '}{', 13, 10, 0],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function stristrProvider(): array
    {
        return [
            ['haystack', 'needle', false],
            ['before match, after match', 'match', 'match, after match'],
            ['Би шил идэй чадна', 'шил', 'шил идэй чадна'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strrevProvider(): array
    {
        return [
            ['abc def', 'fed cba'],
            ['Би шил', 'лиш иБ'],
            ['白日依山盡', '盡山依日白'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strspnProvider(): array
    {
        return [
            ['A321 Main Street', '0123456789', 1, 2, 2],
            ['321 Main Street', '0123456789', 0, 2, 2],
            ['A321 Main Street', '0123456789', 0, 10, 0],
            ['321 Main Street', '0123456789', 0, null, 3],
            ['Main Street 321', '0123456789', 0, -3, 0],
            ['321 Main Street', '0123456789', 0, -13, 2],
            ['321 Main Street', '0123456789', 0, -12, 3],
            ['A321 Main Street', '0123456789', 0, null, 0],
            ['A321 Main Street', '0123456789', 1, 10, 3],
            ['A321 Main Street', '0123456789', 1, null, 3],
            ['Би шил идэй чадна', 'Би', 0, null, 2],
            ['чадна Би шил идэй чадна', 'Би', 0, null, 0],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function substrReplaceProvider(): array
    {
        return [
            ['321 Broadway Avenue', '321 Main Street', 'Broadway Avenue', 4, null],
            ['321 Broadway Street', '321 Main Street', 'Broadway', 4, 4],
            ['чадна 我能吞', 'чадна Би шил идэй чадна', '我能吞', 6, null],
            ['чадна 我能吞 шил идэй чадна', 'чадна Би шил идэй чадна', '我能吞', 6, 2],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function ltrimProvider(): array
    {
        return [
            ['   abc def', null, 'abc def'],
            ['   abc def', '', '   abc def'],
            [' Би шил', null, 'Би шил'],
            ["\t\n\r\x0BБи шил", null, 'Би шил'],
            ["\x0B\t\n\rБи шил", "\t\n\x0B", "\rБи шил"],
            ["\x09Би шил\x0A", "\x09\x0A", "Би шил\x0A"],
            ['1234abc', '0123456789', 'abc'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function rtrimProvider(): array
    {
        return [
            ['abc def   ', null, 'abc def'],
            ['abc def   ', '', 'abc def   '],
            ['Би шил ', null, 'Би шил'],
            ["Би шил\t\n\r\x0B", null, 'Би шил'],
            ["Би шил\r\x0B\t\n", "\t\n\x0B", "Би шил\r"],
            ["\x09Би шил\x0A", "\x09\x0A", "\x09Би шил"],
            ['01234abc', 'abc', '01234'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function trimProvider(): array
    {
        return [
            ['  abc def   ', null, 'abc def'],
            ['  abc def   ', '', '  abc def   '],
            ['   Би шил ', null, 'Би шил'],
            ["\t\n\r\x0BБи шил\t\n\r\x0B", null, 'Би шил'],
            ["\x0B\t\n\rБи шил\r\x0B\t\n", "\t\n\x0B", "\rБи шил\r"],
            ["\x09Би шил\x0A", "\x09\x0A", "Би шил"],
            ['1234abc56789', '0123456789', 'abc'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function ucfirstProvider(): array
    {
        return [
            ['george', 'George'],
            ['мога', 'Мога'],
            ['ψυχοφθόρα', 'Ψυχοφθόρα'],
            ['', ''],
            ['ψ', 'Ψ'],
        ];
    }

    /**
     * lcfirstProvider
     *
     * @return  array
     */
    public function lcfirstProvider(): array
    {
        return [
            ['GEORGE', 'gEORGE'],
            ['МОГА', 'мОГА'],
            ['ΨΥΧΟΦΘΌΡΑ', 'ψΥΧΟΦΘΌΡΑ'],
            ['', ''],
            ['Ψ', 'ψ'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function ucwordsProvider(): array
    {
        return [
            ['george washington', 'George Washington'],
            ["george\r\nwashington", "George\r\nWashington"],
            ['мога', 'Мога'],
            ['αβγ δεζ', 'Αβγ Δεζ'],
            ['åbc öde', 'Åbc Öde'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function convertEncodingProvider(): array
    {
        return [
            ['Åbc Öde €2.0', 'UTF-8', 'ISO-8859-15', "\xc5bc \xd6de \xA42.0"],
            ['', 'UTF-8', 'ISO-8859-15', ''],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function isUtf8Provider(): array
    {
        return [
            ["\xCF\xB0", true],
            ["\xFBa", false],
            ["\xFDa", false],
            ["foo\xF7bar", false],
            ['george Мога Ž Ψυχοφθόρα ฉันกินกระจกได้ 我能吞下玻璃而不伤身体 ', true],
            ["\xFF ABC", false],
            ["\xFa ABC", false],
            ["0xfffd ABC", true],
            ['', true],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function unicodeToUtf8Provider(): array
    {
        return [
            ["\u0422\u0435\u0441\u0442 \u0441\u0438\u0441\u0442\u0435\u043c\u044b", "Тест системы"],
            ["\u00dcberpr\u00fcfung der Systemumstellung", "Überprüfung der Systemumstellung"],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function unicodeToUtf16Provider(): array
    {
        return [
            ["\u0422\u0435\u0441\u0442 \u0441\u0438\u0441\u0442\u0435\u043c\u044b", "Тест системы"],
            ["\u00dcberpr\u00fcfung der Systemumstellung", "Überprüfung der Systemumstellung"],
        ];
    }

    /**
     * providerTestShuffle
     *
     * @return  array
     */
    public function providerTestShuffle(): array
    {
        return [
            ['foo bar'],
            ['∂∆ ˚åß'],
            ['å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬'],
        ];
    }

    /**
     * substr_countProvider
     *
     * @return  array
     */
    public function substrCountProvider(): array
    {
        return [
            ['FooBarFlowerSakura', 'Flower', 1, Utf8String::CASE_SENSITIVE],
            ['FooBarFlowerSakura', 'o', 3, Utf8String::CASE_SENSITIVE],
            ['FooOOooo', 'o', 5, Utf8String::CASE_SENSITIVE],
            ['FooOOooo', 'o', 7, Utf8String::CASE_INSENSITIVE],
            ['FÒÔòôòô', 'ô', 2, Utf8String::CASE_SENSITIVE],
            ['FÒÔòôòô', 'ô', 3, Utf8String::CASE_INSENSITIVE],
            ['объектов на карте с', 'б', 1, Utf8String::CASE_SENSITIVE],
            ['庭院深深深幾許', '深', 3, Utf8String::CASE_SENSITIVE],
        ];
    }
}
