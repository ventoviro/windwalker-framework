<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\DI\Test\Injection;

/**
 * The HelloInner class.
 */
#[HelloWrapper('World')]
class HelloInner
{
    /**
     * HelloInner constructor.
     *
     * @param  mixed  $bar
     */
    public function __construct(public $bar)
    {
    }
}
