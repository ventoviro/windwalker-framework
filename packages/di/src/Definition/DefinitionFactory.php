<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use Closure;
use InvalidArgumentException;

/**
 * The DefinitionFactory class.
 */
class DefinitionFactory
{
    public static function create(mixed $value, int $options = 0): StoreDefinitionInterface
    {
        if ($value instanceof StoreDefinitionInterface) {
            return $value;
        }

        if (!$value instanceof DefinitionInterface) {
            if (!$value instanceof Closure) {
                $value = fn() => $value;
            }

            $value = new ClosureDefinition($value);
        }

        return new StoreDefinition($value, $options);
    }

    public static function wrap(mixed $value): DefinitionInterface
    {
        if ($value instanceof DefinitionInterface) {
            return $value;
        }

        return new ValueDefinition($value);
    }

    public static function isSameClass(mixed $a, mixed $b): bool
    {
        $class1 = static::getClassName($a);
        $class2 = static::getClassName($b);

        return strtolower(trim($class1, '\\')) === strtolower(trim($class2, '\\'));
    }

    public static function getClassName(mixed $obj): mixed
    {
        if ($obj instanceof ObjectBuilderDefinition) {
            return $obj->getClass();
        }

        if ($obj instanceof Closure) {
            return spl_object_hash($obj);
        }

        if (is_object($obj)) {
            return $obj::class;
        }

        if (is_string($obj) || is_callable($obj)) {
            return $obj;
        }

        throw new InvalidArgumentException('Invalid object type, should be object or class name.');
    }
}
