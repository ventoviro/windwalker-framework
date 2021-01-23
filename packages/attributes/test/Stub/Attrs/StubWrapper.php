<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Attributes\Test\Stub\Attrs;

use Windwalker\Attributes\AttributeHandler;

/**
 * The StubWrapper class.
 */
#[\Attribute]
class StubWrapper
{
    public object $instance;

    public function __invoke(AttributeHandler $handler): \Closure
    {
        return function (...$args) use ($handler) {
            $this->instance = $handler(...$args);

            return $this;
        };
    }
}
