<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Attributes;

use Attribute;

/**
 * The AttributeType class.
 */
abstract class AttributeType
{
    public const CLASSES = Attribute::TARGET_CLASS;

    public const FUNCTIONS = Attribute::TARGET_FUNCTION;

    public const METHODS = Attribute::TARGET_METHOD;

    public const PROPERTIES = Attribute::TARGET_PROPERTY;

    public const CLASS_CONSTANTS = Attribute::TARGET_CLASS_CONSTANT;

    public const PARAMETERS = Attribute::TARGET_PARAMETER;

    public const CALLABLE = 1 << 6;

    public const ALL = (1 << 7) - 1;
}
