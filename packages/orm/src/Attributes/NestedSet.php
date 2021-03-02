<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\ORM\NestedSetMapper;

/**
 * The NestedSet class.
 */
#[\Attribute]
class NestedSet extends MapperClass
{
    public const POSITION_BEFORE = 1;
    public const POSITION_AFTER = 2;
    public const POSITION_FIRST_CHILD = 4;
    public const POSITION_LAST_CHILD = 6;

    /**
     * @inheritDoc
     */
    public function __construct(string $className = NestedSetMapper::class)
    {
        parent::__construct($className);
    }
}
