<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Nested;

/**
 * Interface NestedPathableInterface
 */
interface NestedPathableInterface extends NestedEntityInterface
{
    /**
     * @return string
     */
    public function getAlias(): string;

    /**
     * @param  string  $alias
     *
     * @return  static  Return self to support chaining.
     */
    public function setAlias(string $alias): static;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param  string  $path
     *
     * @return  static  Return self to support chaining.
     */
    public function setPath(string $path): static;
}
