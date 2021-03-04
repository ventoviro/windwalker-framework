<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Nested;

use Windwalker\ORM\EntityInterface;

/**
 * The NestedEntityInterface class.
 */
interface NestedEntityInterface extends EntityInterface
{
    /**
     * @return Position
     */
    public function getPosition(): Position;

    public function getPrimaryKeyValue(): mixed;

    /**
     * @return mixed|null
     */
    public function getParentId(): mixed;

    /**
     * @param  mixed|null  $parentId
     *
     * @return  static  Return self to support chaining.
     */
    public function setParentId(mixed $parentId): static;

    /**
     * @return int
     */
    public function getWidth(): int;

    /**
     * childrenCount
     *
     * @return  int
     */
    public function childrenCount(): int;

    /**
     * @return int
     */
    public function getLft(): int;

    /**
     * @param  int  $lft
     *
     * @return  static  Return self to support chaining.
     */
    public function setLft(int $lft): static;

    /**
     * @return int
     */
    public function getRgt(): int;

    /**
     * @param  int  $rgt
     *
     * @return  static  Return self to support chaining.
     */
    public function setRgt(int $rgt): static;

    /**
     * @return int
     */
    public function getLevel(): int;

    /**
     * @param  int  $level
     *
     * @return  static  Return self to support chaining.
     */
    public function setLevel(int $level): static;
}
