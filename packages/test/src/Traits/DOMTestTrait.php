<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Test\Traits;

use Exception;
use Windwalker\DOM\Format\DOMFormatter;
use Windwalker\DOM\Format\HTMLFormatter;

/**
 * The DomTestCase class.
 *
 * @since  2.0
 */
trait DOMTestTrait
{
    use BaseAssertionTrait;

    /**
     * Asserts that two variables are equal.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     */
    public static function assertDomStringEqualsDomString(
        $expected,
        $actual,
        $message = ''
    ): void {
        self::assertEquals(
            DOMFormatter::minify((string) $expected),
            DOMFormatter::minify((string) $actual),
            $message
        );
    }

    /**
     * Asserts that two variables are equal.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     */
    public static function assertDomFormatEquals(
        $expected,
        $actual,
        $message = ''
    ): void {
        self::assertEquals(
            DOMFormatter::format((string) $expected),
            DOMFormatter::format((string) $actual),
            $message
        );
    }

    /**
     * Asserts that two variables are equal.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     *
     * @throws Exception
     */
    public static function assertHtmlFormatEquals(
        $expected,
        $actual,
        $message = ''
    ): void {
        self::assertEquals(
            HTMLFormatter::format((string) $expected),
            HTMLFormatter::format((string) $actual),
            $message
        );
    }
}
