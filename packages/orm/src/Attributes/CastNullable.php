<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;

/**
 * The CastNullable class.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_PROPERTY)]
class CastNullable extends Cast
{
    /**
     * @inheritDoc
     */
    public function __construct(mixed $cast, mixed $extract = null, int $options = 0)
    {
        $options |= static::NULLABLE;

        parent::__construct($cast, $extract, $options);
    }
}
