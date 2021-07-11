<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Test\Component;

use Closure;
use Windwalker\Edge\Component\AbstractComponent;

/**
 * The FooComponent class.
 */
class FooComponent extends AbstractComponent
{
    /**
     * @inheritDoc
     */
    public function render(): Closure|string
    {
        return fn() => 'Foo Hello {!! $slot !!}';
    }
}
