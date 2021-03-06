<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Test\Traits;

use JetBrains\PhpStorm\NoReturn;
use Throwable;
use Windwalker\Data\Format\PhpFormat;
use Windwalker\Utilities\Str;
use Windwalker\Utilities\TypeCast;

/**
 * StringTestTrait
 *
 * @since  {DEPLOY_VERSION}
 */
trait BaseAssertionTrait
{
    /**
     * assertStringDataEquals
     *
     * @param  string  $expected
     * @param  string  $actual
     * @param  string  $message
     *
     * @return  void
     */
    public static function assertStringDataEquals(
        string $expected,
        string $actual,
        string $message = ''
    ): void {
        static::assertEquals(
            Str::collapseWhitespaces($expected),
            Str::collapseWhitespaces($actual),
            $message
        );
    }

    /**
     * assertStringDataEquals
     *
     * @param  string  $expected
     * @param  string  $actual
     * @param  string  $message
     *
     * @return  void
     */
    public static function assertStringSafeEquals(
        string $expected,
        string $actual,
        string $message = ''
    ): void {
        static::assertEquals(
            trim(Str::replaceCRLF($expected)),
            trim(Str::replaceCRLF($actual)),
            $message
        );
    }

    /**
     * assertExpectedException
     *
     * @param  callable          $closure
     * @param  string|Throwable  $class
     * @param  string|null       $msg
     * @param  int|null          $code
     * @param  string            $message
     *
     * @return  void
     */
    public static function assertExpectedException(
        callable $closure,
        string|Throwable $class = Throwable::class,
        ?string $msg = null,
        ?int $code = null,
        string $message = ''
    ): void {
        if (is_object($class)) {
            $class = get_class($class);
        }

        try {
            $closure();
        } catch (Throwable $t) {
            static::assertInstanceOf($class, $t, $message);

            if ($msg !== null) {
                static::assertStringStartsWith($msg, $t->getMessage(), $message);
            }

            if ($code !== null) {
                static::assertEquals($code, $t->getCode(), $message);
            }

            return;
        }

        static::fail('No exception or throwable caught. expected: ' . $class);
    }

    /**
     * Asserts that two associative arrays are similar.
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering
     *
     * @param  array  $expected
     * @param  array  $array
     */
    public static function assertArraySimilar(array $expected, array $array): void
    {
        static::assertEquals([], array_diff_key($array, $expected));

        foreach ($expected as $key => $value) {
            if (is_array($value)) {
                static::assertArraySimilar($value, $array[$key]);
            } else {
                static::assertContains($value, $array);
            }
        }
    }

    public static function dumpArray(mixed $array, array $options = [], bool $asString = false): ?string
    {
        $options['return'] = false;

        $export = (new PhpFormat())->dump(TypeCast::toArray($array, true), $options);

        if ($asString) {
            return $export;
        }

        echo $export;

        return null;
    }
}
