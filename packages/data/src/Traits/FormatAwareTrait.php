<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Data\Traits;

use Windwalker\Data\Format\FormatRegistry;

/**
 * Trait FormatAwareTrait
 */
trait FormatAwareTrait
{
    protected FormatRegistry|null $formatRegistry = null;

    /**
     * @return FormatRegistry
     */
    public function getFormatRegistry(): FormatRegistry
    {
        return $this->formatRegistry ?? new FormatRegistry();
    }

    /**
     * @param  FormatRegistry|null  $formatRegistry
     *
     * @return  static  Return self to support chaining.
     */
    public function setFormatRegistry(?FormatRegistry $formatRegistry): static
    {
        $this->formatRegistry = $formatRegistry;

        return $this;
    }
}
