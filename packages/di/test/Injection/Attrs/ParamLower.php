<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Test\Injection\Attrs;

use Attribute;
use Windwalker\DI\Attributes\AttributeHandler;
use Windwalker\DI\Attributes\ContainerAttributeInterface;
use Windwalker\DI\Attributes\ParameterDecoratorInterface;

/**
 * The ParamLower class.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class ParamLower implements ContainerAttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable
    {
        return fn(...$args) => $handler(...$args)->toLowerCase();
    }
}
