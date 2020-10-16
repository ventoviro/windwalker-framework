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
 * The Length class.
 */
class Length extends AbstractFilter
{
    /**
     * Property max.
     *
     * @var int
     */
    protected int $max;

    /**
     * Property utf8.
     *
     * @var  bool
     */
    protected bool $utf8;

    /**
     * MaxlengthFilter constructor.
     *
     * @param $max
     * @param $utf8
     */
    public function __construct(int $max, bool $utf8 = true)
    {
        $this->max = $max;
        $this->utf8 = $utf8;
    }

    /**
     * clean
     *
     * @param $value
     *
     * @return mixed
     */
    public function filter(mixed $value): mixed
    {
        $value = (string) $value;

        $len = $this->utf8 ? mb_strlen($value) : strlen($value);

        if ($len <= $this->max) {
            return $value;
        }

        return $this->utf8
            ? mb_substr($value, 0, $this->max)
            : substr($value, 0, $this->max);
    }
}
