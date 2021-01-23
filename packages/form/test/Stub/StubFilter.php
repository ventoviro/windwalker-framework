<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Test\Stub;

use Windwalker\Filter\FilterInterface;

/**
 * The StubFilter class.
 *
 * @since  2.0
 */
class StubFilter implements FilterInterface
{
    /**
     * clean
     *
     * @param  string  $value
     *
     * @return  mixed
     */
    public function filter(mixed $value): mixed
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }
}
