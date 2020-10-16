<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractFilter;

/**
 * The Absolute class.
 */
class Absolute extends AbstractFilter
{
    /**
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        return abs($value);
    }
}
