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
 * The MapperClass class.
 */
#[\Attribute]
class MapperClass
{
    /**
     * MapperClass constructor.
     *
     * @param  string  $className
     */
    public function __construct(protected string $className)
    {
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
