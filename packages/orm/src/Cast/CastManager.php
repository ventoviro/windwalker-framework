<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

/**
 * The CastManager class.
 */
class CastManager
{
    protected array $casts = [];

    /**
     * @return array
     */
    public function getCasts(): array
    {
        return $this->casts;
    }

    /**
     * @param  array  $casts
     *
     * @return  static  Return self to support chaining.
     */
    public function setCasts(array $casts): static
    {
        $this->casts = $casts;

        return $this;
    }
}
