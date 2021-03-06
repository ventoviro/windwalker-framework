<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Attributes;

use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The AttributesAccessor class.
 */
class AttributesAccessor
{
    public static function runAttributeIfExists(
        mixed $valueOrAttrs,
        string $attributeClass,
        callable $handler
    ): int {
        $count = 0;

        if (!is_array($valueOrAttrs)) {
            $valueOrAttrs = (array) AttributesAccessor::getAttributesFromAny($valueOrAttrs, $attributeClass);
        }

        /** @var \ReflectionAttribute $attribute */
        foreach ($valueOrAttrs as $attribute) {
            if (strtolower($attribute->getName()) === strtolower($attributeClass)) {
                $handler($attribute->newInstance());
                $count++;
            }
        }

        return $count;
    }

    public static function getFirstAttribute(
        mixed $value,
        string $attributeClass,
        int $flags = 0
    ): ?\ReflectionAttribute {
        $attrs = AttributesAccessor::getAttributesFromAny($value, $attributeClass, $flags);

        return $attrs ? $attrs[0] : null;
    }

    public static function getFirstAttributeInstance(
        mixed $value,
        string $attributeClass,
        int $flags = 0
    ): ?object {
        $attr = AttributesAccessor::getFirstAttribute($value, $attributeClass, $flags);

        return $attr?->newInstance();
    }

    /**
     * Get Attributes from any supported object or class names.
     *
     * @param  mixed        $value
     * @param  string|null  $name
     * @param  int          $flags
     *
     * @return  ?array<int, \ReflectionAttribute>
     */
    public static function getAttributesFromAny(
        mixed $value,
        string|null $name = null,
        int $flags = 0
    ): ?array {
        return ReflectAccessor::reflect($value)?->getAttributes($name, $flags);
    }
}
