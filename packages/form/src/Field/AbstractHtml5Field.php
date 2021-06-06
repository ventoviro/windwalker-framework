<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\Filter\Rule\Range;

/**
 * The AbstractHtml5Field class.
 *
 * @method  $this  step(int|float|string $value)
 * @method  mixed  getStep()
 * @method  $this  pattern(string $value)
 * @method  mixed  getPattern()
 *
 * @since  3.0.1
 */
abstract class AbstractHtml5Field extends TextField
{
    /**
     * max
     *
     * @param  int|null  $max
     * @param  bool      $addFilter
     *
     * @return mixed
     *
     * @since  3.4.2
     */
    public function max(?int $max = null, bool $addFilter = true): mixed
    {
        if ($addFilter) {
            $this->addFilter(new Range(null, $max));
        }

        return $this->attr('max', $max);
    }

    /**
     * min
     *
     * @param  int|null  $min
     * @param  bool      $addFilter
     *
     * @return mixed
     *
     * @since  3.4.2
     */
    public function min(?int $min = null, bool $addFilter = true): mixed
    {
        if ($addFilter) {
            $this->addFilter(new Range($min, null));
        }

        return $this->attr('min', $min);
    }

    /**
     * range
     *
     * @param  int   $min
     * @param  int   $max
     * @param  bool  $addFilter
     * @param  bool  $forceInt
     *
     * @return  $this
     *
     * @since  3.4.2
     */
    public function range(?int $min, ?int $max, bool $addFilter = true): static
    {
        if ($addFilter) {
            $this->addFilter(new Range($min, $max));
        }

        $this->min($min, false)
            ->max($max, false);

        return $this;
    }

    /**
     * getAccessors
     *
     * @return  array
     *
     * @since   3.1.2
     */
    protected function getAccessors(): array
    {
        return array_merge(
            parent::getAccessors(),
            [
                //
            ]
        );
    }
}
