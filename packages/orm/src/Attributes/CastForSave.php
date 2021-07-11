<?php

/**
 * Part of datavideo project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;

/**
 * The CastForSave class.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class CastForSave
{
    /**
     * CastForSave constructor.
     */
    public function __construct(public mixed $caster, public int $options = 0)
    {
    }
}
