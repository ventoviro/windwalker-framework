<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

/**
 * The PK class.
 */
#[\Attribute]
class PK
{
    protected bool $primary;

    /**
     * PK constructor.
     *
     * @param  bool  $primary
     */
    public function __construct(bool $primary = false)
    {
        $this->primary = $primary;
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->primary;
    }
}
