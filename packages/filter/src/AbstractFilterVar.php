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
 * The AbstractFilterVar class.
 */
abstract class AbstractFilterVar extends AbstractFilter
{
    /**
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        return filter_var($value, $this->getFilterName(), (int) $this->getOptions());
    }

    abstract public function getFilterName(): int;

    abstract public function getOptions(): ?int;
}
