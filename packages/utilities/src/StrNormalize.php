<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities;

/**
 * The StrNotmalise class.
 *
 * @since  __DEPLOY_VERSION__
 */
class StrNormalize
{
    public static function splitCamelCase(string $str): array
    {
        return preg_split('/(?<=[^A-Z_])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][^A-Z_])/x', $str);
    }

    public static function splitBy(string $str, string $separator = '_'): array
    {
        return array_filter(explode($separator, $str), 'strlen');
    }

    public static function splitAll(string $str): array
    {
        $strs = preg_split('#[ \-_\.]+#', $str);

        $strs = array_map([static::class, 'splitCamelCase'], $strs);

        return array_values(Arr::flatten($strs));
    }

    /**
     * Separate a string by custom separator.
     *
     * @param  string  $input      The string input (ASCII only).
     * @param  string  $separator  The separator to want to separate it.
     *
     * @return  string  The string be converted.
     *
     * @since   2.1
     */
    public static function separate(string $input, string $separator = '_'): string
    {
        return implode($separator, self::splitAll($input));
    }

    /**
     * Method to convert a string into camel case.
     *
     * @param  string  $input  The string input (ASCII only).
     *
     * @return  string  The camel case string.
     */
    public static function toCamelCase(string $input): string
    {
        return lcfirst(static::toPascalCase($input));
    }

    /**
     * Method to convert a string into pascal case.
     *
     * @param  string  $input  The string input (ASCII only).
     *
     * @return  string  The camel case string.
     *
     * @since   2.0
     */
    public static function toPascalCase(string $input): string
    {
        return implode('', array_map('ucfirst', self::splitAll($input)));
    }

    /**
     * Method to convert a string into dash separated form.
     *
     * @param  string  $input  The string input (ASCII only).
     *
     * @return  string  The dash separated string.
     *
     * @since   2.0
     */
    public static function toDashSeparated(string $input): string
    {
        return self::separate($input, '-');
    }

    /**
     * Dash separated alias.
     *
     * @param  string  $input
     *
     * @return  string
     */
    public static function toKebabCase(string $input): string
    {
        return strtolower(static::toDashSeparated($input));
    }

    /**
     * Method to convert a string into space separated form.
     *
     * @param  string  $input  The string input (ASCII only).
     *
     * @return  string  The space separated string.
     *
     * @since   2.0
     */
    public static function toSpaceSeparated(string $input): string
    {
        // Convert underscores and dashes to spaces.
        return static::separate($input, ' ');
    }

    /**
     * Method to convert a string into dot separated form.
     *
     * @param  string  $input  The string input (ASCII only).
     *
     * @return  string  The dot separated string.
     *
     * @since   2.1
     */
    public static function toDotSeparated(string $input): string
    {
        // Convert underscores and dashes to dots.
        return static::separate($input, '.');
    }

    /**
     * Method to convert a string into underscore separated form.
     *
     * @param  string  $input  The string input (ASCII only).
     *
     * @return  string  The underscore separated string.
     *
     * @since   2.0
     */
    public static function toSnakeCase(string $input): string
    {
        // Convert spaces and dashes to underscores.
        return static::separate($input, '_');
    }

    /**
     * Alias of toSnakeCase()
     *
     * @param  string  $input
     *
     * @return  string
     */
    public static function toUnderscoreSeparated(string $input): string
    {
        return static::toSnakeCase($input);
    }

    /**
     * Method to convert a string into variable form.
     *
     * @param  string  $input  The string input (ASCII only).
     *
     * @return  string  The variable string.
     *
     * @since   2.0
     */
    public static function toVariable(string $input): string
    {
        // Remove dashes and underscores, then convert to camel case.
        $input = self::toCamelCase($input);

        // Remove leading digits.
        $input = preg_replace('#^[0-9]+.*$#', '', $input);

        // Lowercase the first character.
        $input[0] = strtolower($input[0] ?? '');

        return $input;
    }

    /**
     * Method to convert a string into key form.
     *
     * @param  string  $input  The string input (ASCII only).
     *
     * @return  string  The key string.
     *
     * @since   2.0
     */
    public static function toKey(string $input): string
    {
        // Remove spaces and dashes, then convert to lower case.
        $input = self::toUnderscoreSeparated($input);
        $input = strtolower($input);

        return $input;
    }

    /**
     * Convert to standard PSR-0/PSR-4 class name.
     *
     * @param  string  $class  The class name string.
     *
     * @return  string Normalised class name.
     *
     * @since   2.0
     */
    public static function toClassNamespace(string $class): string
    {
        $class = trim($class, '\\');

        $class = (string) str_replace(['\\', '/'], ' ', $class);

        $class = ucwords($class);

        return str_replace(' ', '\\', $class);
    }
}
