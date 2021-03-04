<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Nested;

use Windwalker\ORM\Attributes\Column;

/**
 * The NestedPathableTrait class.
 */
trait NestedPathableTrait
{
    use NestedEntityTrait;

    #[Column('alias')]
    protected string $alias = '';

    #[Column('path')]
    protected string $path = '';

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param  string  $alias
     *
     * @return  static  Return self to support chaining.
     */
    public function setAlias(string $alias): static
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param  string  $path
     *
     * @return  static  Return self to support chaining.
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }
}
