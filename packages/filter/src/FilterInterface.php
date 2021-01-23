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
 * Interface FilterInterface
 */
interface FilterInterface
{
    /**
     * Clean value.
     *
     * @param  mixed  $value
     *
     * @return mixed
     */
    public function filter(mixed $value): mixed;
}
