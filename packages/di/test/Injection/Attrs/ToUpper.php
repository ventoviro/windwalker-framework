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

/**
 * The ToUpper class.
 */
#[Attribute]
class ToUpper implements ContainerAttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable
    {
        return fn(...$args) => strtoupper($handler(...$args));
    }
}
