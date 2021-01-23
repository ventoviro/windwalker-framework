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
 * The Range class.
 */
class Range extends AbstractFilter
{
    protected int|float|null $min;

    protected int|float|null $max;

    /**
     * RangeFilter constructor.
     *
     * @param float $min
     * @param float $max
     */
    public function __construct(int|float|null $min = null, int|float|null $max = null)
    {
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        if ($this->min !== null && $value < $this->min) {
            $value = $this->min;
        }

        if ($this->max !== null && $value > $this->max) {
            $value = $this->max;
        }

        return $value;
    }
}
