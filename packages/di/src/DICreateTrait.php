<?php

/**
 * Part of datavideo project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\DI;

/**
 * Trait DICreateTrait
 */
trait DICreateTrait
{
    public static function di(...$args): Definition\ObjectBuilderDefinition
    {
        return create(
            static::class,
            ...$args
        );
    }
}
