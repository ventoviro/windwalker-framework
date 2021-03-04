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
class NestedSet extends Table
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name, ?string $alias = null, ?string $mapperClass = NestedSetMapper::class)
    {
        parent::__construct($name, $alias, $mapperClass);
    }
}
