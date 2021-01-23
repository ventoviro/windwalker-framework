<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Definition;

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
            if (!$value instanceof \Closure) {
                $value = fn () => $value;
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
}
