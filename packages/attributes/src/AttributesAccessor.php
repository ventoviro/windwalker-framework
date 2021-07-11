<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Attributes;

use ReflectionAttribute;
use ReflectionClass;
use Windwalker\Filesystem\Filesystem;
use Windwalker\Utilities\Reflection\ReflectAccessor;
use Windwalker\Utilities\StrNormalize;

/**
 * The AttributesAccessor class.
 */
class AttributesAccessor
{
    public static function runAttributeIfExists(
        mixed $valueOrAttrs,
        string $attributeClass,
        callable $handler,
        int $options = 0
    ): int {
        $count = 0;
        $source = $valueOrAttrs;

        if (!is_array($valueOrAttrs)) {
            $valueOrAttrs = (array) static::getAttributesFromAny($valueOrAttrs, $attributeClass);
        }

        /** @var ReflectionAttribute $attribute */
        foreach ($valueOrAttrs as $attribute) {
            $match = strtolower($attribute->getName()) === strtolower($attributeClass);

            if (!$match && $options & ReflectionAttribute::IS_INSTANCEOF) {
                $match = is_subclass_of($attribute->getName(), $attributeClass);
            }

            if ($match) {
                $handler($attribute->newInstance(), $source);
                $count++;
            }
        }

        return $count;
    }

    /**
     * scanDirAndRunAttributes
     *
     * @param  string    $attributeClass
     * @param  string    $dir
     * @param  string    $namespace
     * @param  callable  $handler
     * @param  int       $options
     *
     * @return  array<string, int>
     */
    public static function scanDirAndRunAttributes(
        string $attributeClass,
        string $dir,
        string $namespace,
        callable $handler,
        int $options = 0
    ): array {
        $files = Filesystem::files($dir, true);
        $results = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $class = $namespace . '\\' . $file->getRelativePath() . '\\' . $file->getBasename('.php');
            $class = StrNormalize::toClassNamespace($class);

            if (class_exists($class)) {
                $results[$class] = static::runAttributeIfExists(
                    $class,
                    $attributeClass,
                    function ($attrInstance, $source) use ($file, $class, $handler) {
                        return $handler($attrInstance, $class, $file);
                    },
                    $options
                );
            }
        }

        return $results;
    }

    public static function getFirstAttribute(
        mixed $value,
        string $attributeClass,
        int $flags = 0
    ): ?ReflectionAttribute {
        $attrs = static::getAttributesFromAny($value, $attributeClass, $flags);

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
     * @return  ?array<int, ReflectionAttribute>
     */
    public static function getAttributesFromAny(
        mixed $value,
        string|null $name = null,
        int $flags = 0
    ): ?array {
        $ref = ReflectAccessor::reflect($value);

        if (!$ref) {
            return null;
        }

        $attrs[] = $ref->getAttributes($name, $flags);

        if ($ref instanceof ReflectionClass) {
            while ($ref = $ref->getParentClass()) {
                $attrs[] = $ref->getAttributes($name, $flags);
            }
        }

        return array_merge(...$attrs);
    }
}
