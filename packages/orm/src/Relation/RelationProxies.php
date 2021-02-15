<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation;

/**
 * The RelationProxy class.
 */
class RelationProxies
{
    protected static ?\WeakMap $instances = null;

    public static function set(object $entity, string $prop, callable $getter): void
    {
        self::getMap()[$entity] ??= [];

        self::getMap()[$entity][$prop] = $getter;
    }

    public static function get(object $entity, string $prop): mixed
    {
        return self::getMap()[$entity][$prop] ?? null;
    }

    public static function has(object $entity, string $prop): bool
    {
        return isset(self::getMap()[$entity][$prop]);
    }

    public static function call(object $entity, string $prop): mixed
    {
        $result = self::get($entity, $prop);

        if (!$result) {
            return null;
        }

        if ($result instanceof \Closure) {
            self::getMap()[$entity][$prop] = $result = $result();
        }

        return $result;
    }

    public static function remove(object $entity, string $prop): void
    {
        self::getMap()[$entity][$prop] = null;
    }

    public static function getMap(): \WeakMap
    {
        return self::$instances ??= new \WeakMap();
    }
}
