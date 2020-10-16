<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter;

/**
 * The ValidatorInterface class.
 *
 * @since  2.0
 */
interface ValidatorInterface
{
    /**
     * Test this value.
     *
     * @param  mixed  $value
     *
     * @param  bool   $strict
     *
     * @return  bool
     */
    public function test(mixed $value, bool $strict = false): bool;
}
