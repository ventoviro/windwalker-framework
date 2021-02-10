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
 * The Cast class.
 */
#[\Attribute]
class Cast
{
    protected string $castClass;

    /**
     * Cast constructor.
     *
     * @param  string  $castClass
     */
    public function __construct(string $castClass)
    {
        $this->castClass = $castClass;
    }

    /**
     * @return string
     */
    public function getCastClass(): string
    {
        return $this->castClass;
    }
}
