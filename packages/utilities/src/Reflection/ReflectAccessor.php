<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Reflection;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Windwalker\Utilities\Cache\RuntimeCacheTrait;
use Windwalker\Utilities\TypeCast;

/**
 * The Reflector class.
 */
class ReflectAccessor
{
    use RuntimeCacheTrait;

    public static function getPropertiesValues(
        object|string $object,
        int $filters = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED
    ): array {
        $ref = new ReflectionClass($object);

        $properties = $ref->getProperties($filters);

        $values = [];

        foreach ($properties as $property) {
            $property->setAccessible(true);

            $inited = is_object($object)
                ? $property->isInitialized($object)
                : $property->isInitialized();

            $value = $inited ? $property->getValue($object) : null;

            $values[$property->getName()] = $value;
        }

        return $values;
    }

    /**
     * Helper method that sets a protected or private property in a class by relfection.
     *
     * @param  object  $object        The object for which to set the property.
     * @param  string  $propertyName  The name of the property to set.
     * @param  mixed   $value         The value to set for the property.
     * @param  bool    $safe          Guess and try type casting.
     *
     * @return  void
     *
     * @throws ReflectionException
     * @since   2.0
     */
    public static function setValue(object $object, mixed $propertyName, mixed $value, bool $safe = false): void
    {
        $refl = new ReflectionClass($object);

        $propertyName = (string) $propertyName;

        // First check if the property is easily accessible.
        if ($refl->hasProperty($propertyName)) {
            $property = $refl->getProperty($propertyName);
            $property->setAccessible(true);

            if ($safe) {
                $value = static::safeTypeCast($property, $value);
            }

            $property->setValue($object, $value);

            return;
        }

        $parent = get_parent_class($object);

        if ($parent) {
            $refl = new ReflectionClass($parent);

            if ($refl->hasProperty($propertyName)) {
                // Hrm, maybe dealing with a private property in the parent class.
                $property = new ReflectionProperty($parent, $propertyName);
                $property->setAccessible(true);

                if ($safe) {
                    $value = static::safeTypeCast($property, $value);
                }

                $property->setValue($object, $value);

                return;
            }
        }

        $object->$propertyName = $value;
    }

    protected static function safeTypeCast(\ReflectionProperty $prop, mixed $value): mixed
    {
        $typeRef = $prop->getType();

        if (!$typeRef) {
            return $value;
        }

        if ($typeRef instanceof \ReflectionUnionType) {
            $types = $typeRef->getTypes();
        } else {
            $types = [$typeRef];
        }

        foreach ($types as $type) {
            if (is_object($value) && $value::class === $type->getName()) {
                return $value;
            }

            if ($type->getName() === 'mixed') {
                return $value;
            }

            if ($value === null && $type->allowsNull()) {
                return null;
            }

            if (!$type->isBuiltin()) {
                continue;
            }

            $value = TypeCast::try($value, $type->getName());

            if ($value !== null) {
                return $value;
            }
        }

        return $value ?? settype($value, $types[0]->getName());
    }

    public static function hasProperty(object $object, string $propertyName): bool
    {
        $refl = new ReflectionClass($object);

        // First check if the property is easily accessible.
        $exists = $refl->hasProperty($propertyName);

        // Check parent private
        if (!$exists) {
            $parent = get_parent_class($object);

            $refl = new ReflectionClass($parent);

            $exists = $refl->hasProperty($propertyName);
        }

        return $exists;
    }

    /**
     * Helper method that gets a protected or private property in a class by relfection.
     *
     * @param  object  $object        The object from which to return the property value.
     * @param  string  $propertyName  The name of the property to return.
     *
     * @return  mixed  The value of the property.
     *
     * @throws ReflectionException
     * @since   2.0
     */
    public static function getValue(object $object, string $propertyName): mixed
    {
        $ref = new ReflectionClass($object);

        // First check if the property is easily accessible.
        if ($ref->hasProperty($propertyName)) {
            $property = $ref->getProperty($propertyName);
            $property->setAccessible(true);

            return $property->getValue($object);
        }

        // Hrm, maybe dealing with a private property in the parent class.
        if (get_parent_class($object)) {
            $property = new ReflectionProperty(get_parent_class($object), $propertyName);
            $property->setAccessible(true);

            return $property->getValue($object);
        }

        throw new InvalidArgumentException(
            sprintf(
                'Invalid property [%s] for class [%s]',
                $propertyName,
                get_class($object)
            )
        );
    }

    /**
     * Helper method that invokes a protected or private method in a class by reflection.
     *
     * Example usage:
     *
     * $this->asserTrue(TestCase::invoke('methodName', $this->object, 123));
     *
     * @param  object  $object      The object on which to invoke the method.
     * @param  string  $methodName  The name of the method to invoke.
     * @param  array   $args        Arguments.
     *
     * @return  mixed
     *
     * @throws ReflectionException
     * @since   2.0
     */
    public static function invoke(object $object, string $methodName, ...$args): mixed
    {
        $method = new ReflectionMethod($object, $methodName);
        $method->setAccessible(true);

        return $method->invokeArgs(is_object($object) ? $object : null, $args);
    }

    public static function reflect(mixed $value): \Reflector
    {
        if ($value instanceof \Reflector) {
            return $value;
        }

        if (is_string($value) && class_exists($value)) {

            return new ReflectionClass($value);
        }

        if (is_object($value) && !$value instanceof \Closure) {
            return new \ReflectionObject($value);
        }

        if (is_callable($value)) {
            return (new ReflectionCallable($value))->getReflector();
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Unable to reflect value of: %s',
                get_debug_type($value)
            )
        );
    }

    public static function getReflectProperties(
        object|string $object,
        ?int $filters = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED
    ): array {
        $ref = new ReflectionClass($object);

        $properties = $ref->getProperties($filters);

        $values = [];

        foreach ($properties as $property) {
            $values[$property->getName()] = $property;
        }

        return $values;
    }

    /**
     * getAssocProperties
     *
     * @param  string|object  $object
     * @param  int|null       $filters
     *
     * @return  ReflectionMethod[]
     * @throws ReflectionException
     */
    public static function getReflectMethods(
        string|object $object,
        ?int $filters = ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PRIVATE
    ): array {
        $ref = new ReflectionClass($object);
        $methods = [];

        foreach ($ref->getMethods($filters) as $method) {
            $methods[$method->getName()] = $method;
        }

        return $methods;
    }

    /**
     * getNoRepeatAttributes
     *
     * @param  \Reflector   $ref
     * @param  string|null  $name
     * @param  int          $flags
     *
     * @return  \ReflectionAttribute[]
     */
    public static function getNoRepeatAttributes(\Reflector $ref, ?string $name = null, int $flags = 0): array
    {
        $attrs = [];

        /** @var \ReflectionAttribute $attribute */
        foreach ($ref->getAttributes($name, $flags) as $attribute) {
            $attrs[$attribute->getName()] = $attribute;
        }

        return $attrs;
    }
}
