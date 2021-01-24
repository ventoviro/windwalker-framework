<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Test\Mock;

/**
 * The Foo class.
 *
 * @since  2.0
 */
class Foo
{
    /**
     * Property bar.
     *
     * @var  Bar
     */
    public $bar = null;

    /**
     * Class init.
     *
     * @param  Bar  $bar
     */
    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }
}
