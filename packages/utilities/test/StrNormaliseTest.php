<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\StrNormalize;

/**
 * StrNormaliseTest
 *
 * @since  2.0
 */
class StrNormaliseTest extends TestCase
{
    /**
     * Method to seed data to testFromCamelCase.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function providerSplitCamelCase(): array
    {
        return [
            // Note: string, expected
            ['FooBarABCDef', ['Foo', 'Bar', 'ABC', 'Def']],
            ['JFooBar', ['J', 'Foo', 'Bar']],
            ['J001FooBar002', ['J001', 'Foo', 'Bar002']],
            ['abcDef', ['abc', 'Def']],
            ['abc_defGhi_Jkl', ['abc_def', 'Ghi_Jkl']],
            ['ThisIsA_NASAAstronaut', ['This', 'Is', 'A_NASA', 'Astronaut']],
            ['JohnFitzgerald_Kennedy', ['John', 'Fitzgerald_Kennedy']],
        ];
    }

    /**
     * Method to seed data to testToCamelCase.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function seedTestToPascalCase(): array
    {
        return [
            ['FooBar', 'Foo Bar'],
            ['FooBar', 'Foo-Bar'],
            ['FooBar', 'Foo_Bar'],
            ['FooBar', 'foo bar'],
            ['FooBar', 'foo-bar'],
            ['FooBar', 'foo_bar'],
        ];
    }

    /**
     * Method to seed data to testToDashSeparated.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function seedTestToKebabCase(): array
    {
        return [
            ['foo-bar', 'Foo Bar'],
            ['foo-bar', 'Foo-Bar'],
            ['foo-bar', 'Foo_Bar'],
            ['foo-bar', 'foo bar'],
            ['foo-bar', 'foo-bar'],
            ['foo-bar', 'foo_bar'],
            ['foo-bar', 'foo   bar'],
            ['foo-bar', 'foo---bar'],
            ['foo-bar', 'foo___bar'],
        ];
    }

    /**
     * Method to seed data to testToSpaceSeparated.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function seedTestToSpaceSeparated(): array
    {
        return [
            ['Foo Bar', 'Foo Bar'],
            ['Foo Bar', 'Foo-Bar'],
            ['Foo Bar', 'Foo_Bar'],
            ['foo bar', 'foo bar'],
            ['foo bar', 'foo-bar'],
            ['foo bar', 'foo_bar'],
            ['foo bar', 'foo   bar'],
            ['foo bar', 'foo---bar'],
            ['foo bar', 'foo___bar'],
        ];
    }

    /**
     * Method to seed data to testToUnderscoreSeparated.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function seedTestToUnderscoreSeparated(): array
    {
        return [
            ['Foo_Bar', 'Foo Bar'],
            ['Foo_Bar', 'Foo-Bar'],
            ['Foo_Bar', 'Foo_Bar'],
            ['foo_bar', 'foo bar'],
            ['foo_bar', 'foo-bar'],
            ['foo_bar', 'foo_bar'],
            ['foo_bar', 'foo   bar'],
            ['foo_bar', 'foo---bar'],
            ['foo_bar', 'foo___bar'],
        ];
    }

    /**
     * Method to seed data to testToVariable.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function seedTestToVariable(): array
    {
        return [
            ['myFooBar', 'My Foo Bar'],
            ['myFooBar', 'My Foo-Bar'],
            ['myFooBar', 'My Foo_Bar'],
            ['myFooBar', 'my foo bar'],
            ['myFooBar', 'my foo-bar'],
            ['myFooBar', 'my foo_bar'],
        ];
    }

    /**
     * Method to seed data to testToKey.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function seedTestToKey(): array
    {
        return [
            ['foo_bar', 'Foo Bar'],
            ['foo_bar', 'Foo-Bar'],
            ['foo_bar', 'Foo_Bar'],
            ['foo_bar', 'foo bar'],
            ['foo_bar', 'foo-bar'],
            ['foo_bar', 'foo_bar'],
        ];
    }

    /**
     * Method to test StrNormalise::fromCamelCase(string, true).
     *
     * @param  string  $input     The input value for the method.
     * @param  string  $expected  The expected value from the method.
     *
     * @return  void
     *
     * @dataProvider  providerSplitCamelCase
     */
    public function testSplitCamelCase($input, $expected)
    {
        $this->assertEquals($expected, StrNormalize::splitCamelCase($input));
    }

    /**
     * Method to test StrNormalise::PascalCase().
     *
     * @param  string  $expected  The expected value from the method.
     * @param  string  $input     The input value for the method.
     *
     * @return  void
     *
     * @dataProvider  seedTestToPascalCase
     * @since         2.0
     */
    public function testToPascalCase($expected, $input)
    {
        $this->assertEquals($expected, StrNormalize::toPascalCase($input));
    }

    /**
     * Method to test StrNormalise::PascalCase().
     *
     * @param  string  $expected  The expected value from the method.
     * @param  string  $input     The input value for the method.
     *
     * @return  void
     *
     * @dataProvider  seedTestToPascalCase
     */
    public function testToCamelCase($expected, $input)
    {
        $this->assertEquals(lcfirst($expected), StrNormalize::toCamelCase($input));
    }

    /**
     * Method to test StrNormalise::testToKebabCase().
     *
     * @param  string  $expected  The expected value from the method.
     * @param  string  $input     The input value for the method.
     *
     * @return  void
     *
     * @dataProvider  seedTestToKebabCase
     * @since         2.0
     */
    public function testToKebabCase($expected, $input)
    {
        $this->assertEquals($expected, StrNormalize::toKebabCase($input));
    }

    /**
     * Method to test StrNormalise::toSpaceSeparated().
     *
     * @param  string  $expected  The expected value from the method.
     * @param  string  $input     The input value for the method.
     *
     * @return  void
     *
     * @dataProvider  seedTestToSpaceSeparated
     * @since         2.0
     */
    public function testToSpaceSeparated($expected, $input)
    {
        $this->assertEquals($expected, StrNormalize::toSpaceSeparated($input));
    }

    /**
     * Method to test StrNormalise::toUnderscoreSeparated().
     *
     * @param  string  $expected  The expected value from the method.
     * @param  string  $input     The input value for the method.
     *
     * @return  void
     *
     * @dataProvider  seedTestToUnderscoreSeparated
     * @since         2.0
     */
    public function testToUnderscoreSeparated($expected, $input)
    {
        $this->assertEquals($expected, StrNormalize::toUnderscoreSeparated($input));
    }

    /**
     * Method to test StrNormalise::toVariable().
     *
     * @param  string  $expected  The expected value from the method.
     * @param  string  $input     The input value for the method.
     *
     * @return  void
     *
     * @dataProvider  seedTestToVariable
     * @since         2.0
     */
    public function testToVariable($expected, $input)
    {
        $this->assertEquals($expected, StrNormalize::toVariable($input));
    }

    /**
     * Method to test StrNormalise::toKey().
     *
     * @param  string  $expected  The expected value from the method.
     * @param  string  $input     The input value for the method.
     *
     * @return  void
     *
     * @dataProvider  seedTestToKey
     * @since         2.0
     */
    public function testToKey($expected, $input)
    {
        $this->assertEquals($expected, StrNormalize::toKey($input));
    }
}
