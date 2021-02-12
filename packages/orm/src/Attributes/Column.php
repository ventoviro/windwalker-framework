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
 * The Column class.
 */
#[\Attribute]
class Column
{
    protected string $name;

    protected \ReflectionProperty $property;

    /**
     * Column constructor.
     *
     * @param  string  $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \ReflectionProperty
     */
    public function getProperty(): \ReflectionProperty
    {
        return $this->property;
    }

    /**
     * @param  \ReflectionProperty  $property
     *
     * @return  static  Return self to support chaining.
     */
    public function setProperty(\ReflectionProperty $property): static
    {
        $this->property = $property;

        return $this;
    }
}
