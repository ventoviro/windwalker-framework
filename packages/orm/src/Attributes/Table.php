<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\Utilities\StrInflector;

/**
 * The Table class.
 */
#[\Attribute]
class Table
{
    protected string $name;

    protected ?string $alias = null;

    /**
     * Table constructor.
     *
     * @param  string       $name
     * @param  string|null  $alias
     */
    public function __construct(string $name, ?string $alias = null)
    {
        $this->name = $name;
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias ?? StrInflector::toSingular($this->name);
    }
}
