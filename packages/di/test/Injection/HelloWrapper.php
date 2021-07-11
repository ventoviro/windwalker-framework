<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\DI\Test\Injection;

use Attribute;
use Windwalker\DI\Attributes\AttributeHandler;
use Windwalker\DI\Attributes\ContainerAttributeInterface;

/**
 * The HelloWrapper class.
 */
#[Attribute]
class HelloWrapper implements ContainerAttributeInterface
{
    /**
     * HelloWrapper constructor.
     *
     * @param $foo
     */
    public function __construct(public $foo)
    {
        //
    }

    public function __invoke(AttributeHandler $handler): callable
    {
        return function (...$args) use ($handler) {
            return $this;
        };
    }
}
