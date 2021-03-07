<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities;

/**
 * The Serial class.
 */
class Serial
{
    protected static array $sequences = [];

    protected static ?\WeakMap $map = null;

    public static function get(string|object $name): int
    {
        if (is_string($name)) {
            static::$sequences[$name] ??= 0;

            return static::$sequences[$name]++;
        }

        $map = static::getMap();

        $map[$name] ??= 0;

        return $map[$name]++;
    }

    public static function set(string|object $name, int $value): void
    {
        if (is_string($name)) {
            static::$sequences[$name] = $value;
            return;
        }

        $map = static::getMap();

        $map[$name] = $value;
    }

    public static function reset(string|object $name): void
    {
        if (is_string($name)) {
            static::$sequences[$name] = 0;
            return;
        }

        $map = static::getMap();

        $map[$name] = 0;
    }

    public static function resetAll(): void
    {
        static::$sequences = [];

        static::resetMap();
    }

    protected static function resetMap(): \WeakMap
    {
        return static::$map = new \WeakMap();
    }

    protected static function getMap(): \WeakMap
    {
        return static::$map ??= static::resetMap();
    }
}
