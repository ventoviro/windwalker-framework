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
 * The NestedEntityTrait class.
 */
trait NestedEntityTrait
{
    protected ?Position $position = null;

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position ??= new Position();
    }
}
