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
    /**
     * @inheritDoc
     */
    public function __construct(string $className = NestedSetMapper::class)
    {
        parent::__construct($className);
    }
}
