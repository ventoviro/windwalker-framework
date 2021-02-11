<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Attributes;

use Windwalker\Utilities\Classes\ObjectBuilder;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\Reflection\ReflectAccessor;
use Windwalker\Utilities\Reflection\ReflectionCallable;

/**
 * The AttributesResolver class.
 */
class AttributesResolver extends ObjectBuilder
{
    use OptionAccessTrait;

    protected array $registry = [];

    /**
     * AttributesResolver constructor.
     *
     * @param  array  $options
     */
    public function __construct(array $options = [])
    {
        $this->prepareOptions(
            [],
            $options
        );
    }

    /**
     * Create object by class and resolve attributes.
     *
     * @param  string  $class
     * @param  mixed   ...$args
     *
     * @return  object
     * @throws \ReflectionException
     */
    public function createObject(string $class, ...$args): object
    {
        $ref         = new \ReflectionClass($class);
        $constructor = $ref->getConstructor();

        if ($constructor) {
            $args = $this->resolveCallArguments($constructor, $args);
        }

        return $this->resolveClassCreate($class)(...$args);
    }

    /**
     * Resolve class constructor and return create function.
     *
     * @param  string         $class
     * @param  callable|null  $builder
     *
     * @return  AttributeHandler
     *
     * @throws \ReflectionException
     */
    public function resolveClassCreate(string $class, ?callable $builder = null): AttributeHandler
    {
        $ref = new \ReflectionClass($class);

        $builder = $builder ?? fn(...$args) => $this->getBuilder()($class, ...$args);

        $handler = $this->createHandler($builder, $ref);

        foreach ($ref->getAttributes() as $attribute) {
            if ($this->hasAttribute($attribute, \Attribute::TARGET_CLASS)) {
                $handler = $this->runAttribute($attribute, $handler);
            }
        }

        return $handler;
    }

    /**
     * Decorate object by attributes.
     *
     * @param  object  $object
     *
     * @return  object
     */
    public function decorateObject(object $object): object
    {
        return $this->resolveObjectDecorate($object)();
    }

    /**
     * Resolve object decorate function.
     *
     * @param  object  $object
     *
     * @return  AttributeHandler
     */
    public function resolveObjectDecorate(object $object): AttributeHandler
    {
        $ref = new \ReflectionObject($object);

        $builder = $this->createHandler(fn() => $object, $ref);

        foreach ($ref->getAttributes() as $attribute) {
            if ($this->hasAttribute($attribute, \Attribute::TARGET_CLASS)) {
                $builder = $this->runAttribute($attribute, $this->createHandler($builder, $ref, $object));
            }
        }

        return $builder;
    }

    /**
     * call
     *
     * @param  callable     $callable
     * @param  mixed        ...$args
     * @param  object|null  $context
     *
     * @return mixed
     */
    public function call(callable $callable, $args = [], ?object $context = null): mixed
    {
        $args = $this->resolveCallArguments($callable, $args);

        return $this->resolveCallable($callable, $context)(...$args);
    }

    public function resolveCallable(callable $callable, ?object $context = null): AttributeHandler
    {
        $ref     = new ReflectionCallable($callable);
        $funcRef = $ref->getReflector();

        $closure = $ref->getClosure();

        if ($context) {
            $closure = $closure->bindTo($context, $context);
        }

        $handler = $this->createHandler($closure, $ref, $ref->getObject());

        foreach ($funcRef->getAttributes() as $attribute) {
            if ($this->hasAttribute($attribute, AttributeType::CALLABLE)) {
                $handler = $this->runAttribute($attribute, $handler);
            }
        }

        return $handler;
    }

    public function resolveCallArguments(callable|\ReflectionFunctionAbstract $ref, array $args): array
    {
        if (!$ref instanceof \ReflectionFunctionAbstract) {
            $callableRef = new ReflectionCallable($ref);
            $ref         = $callableRef->getReflector();
        }

        $parameters = $ref->getParameters();
        $newArgs    = [];

        foreach ($parameters as $i => $parameter) {
            $key = $parameter->getName();

            if (array_key_exists($parameter->getName(), $args)) {
                $newArgs[$key] = &$args[$parameter->getName()];
            } elseif (array_key_exists($i, $args)) {
                $newArgs[$key] = &$args[$i];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $newArgs[$key] = $parameter->getDefaultValue();
            }

            $newArgs[$key] = &$this->resolveParameter($newArgs[$key], $parameter);
        }

        return $newArgs;
    }

    public function &resolveParameter(&$value, \ReflectionParameter $ref): mixed
    {
        $func = fn() => $value;

        $handler = $this->createHandler($func, $ref);

        foreach ($ref->getAttributes() as $attribute) {
            if ($this->hasAttribute($attribute, \Attribute::TARGET_PARAMETER)) {
                $handler = $this->runAttribute($attribute, $handler);
            }
        }

        $value = $handler();

        return $value;
    }

    public function resolveProperties(object $instance): object
    {
        $ref = new \ReflectionObject($instance);

        foreach ($ref->getProperties() as $property) {
            if ($property->isPrivate() || $property->isProtected()) {
                $property->setAccessible(true);
            }

            $getter = fn() => $property->getValue($instance);

            foreach ($property->getAttributes() as $attribute) {
                if ($this->hasAttribute($attribute, \Attribute::TARGET_PROPERTY)) {
                    $getter = $this->runAttribute($attribute, $this->createHandler($getter, $property, $instance));
                }
            }

            $getter();

            if ($property->isPrivate() || $property->isProtected()) {
                $property->setAccessible(false);
            }
        }

        return $instance;
    }

    public function resolveMethods(object $instance): object
    {
        $ref = new \ReflectionObject($instance);

        foreach ($ref->getMethods() as $method) {
            $getter = fn(): array => [$instance, $method->getName()];

            foreach ($method->getAttributes() as $attribute) {
                if ($this->hasAttribute($attribute, \Attribute::TARGET_METHOD)) {
                    $getter = $this->runAttribute($attribute, $this->createHandler($getter, $method, $instance));
                }
            }

            $getter();
        }

        return $instance;
    }

    public function resolveConstants(object $instance): object
    {
        $ref = new \ReflectionObject($instance);

        /** @var \ReflectionClassConstant $constant */
        foreach ($ref->getReflectionConstants() as $constant) {
            $getter = fn(): array => [$instance, $constant];

            foreach ($constant->getAttributes() as $attribute) {
                if ($this->hasAttribute($attribute, \Attribute::TARGET_METHOD)) {
                    $getter = $this->runAttribute($attribute, $this->createHandler($getter, $constant, $instance));
                }
            }

            $getter();
        }

        return $instance;
    }

    public function resolveObjectMembers(object $instance): object
    {
        $instance = $this->resolveConstants($instance);
        $instance = $this->resolveMethods($instance);

        return $this->resolveProperties($instance);
    }

    public function hasAttribute(
        string|\ReflectionAttribute $attributeClass,
        int $target = \Attribute::TARGET_ALL
    ): bool {
        if ($attributeClass instanceof \ReflectionAttribute) {
            $attributeClass = $attributeClass->getName();
        }

        $attr = $this->registry[strtolower($attributeClass)] ?? null;

        if (!$attr) {
            return false;
        }

        return (bool) ($attr[1] & $target);
    }

    /**
     * registerAttribute
     *
     * @param  string  $attributeClass
     * @param  int     $target
     *
     * @return  \Windwalker\DI\Attributesstatic
     */
    public function registerAttribute(string $attributeClass, int $target = \Attribute::TARGET_ALL): static
    {
        $this->registry[strtolower($attributeClass)] ??= [
            strtolower($attributeClass),
            $target,
        ];

        $this->registry[strtolower($attributeClass)][1] |= $target;

        return $this;
    }

    /**
     * removeAttribute
     *
     * @param  string  $attributeClass
     * @param  int     $target
     *
     * @return  \Windwalker\DI\Attributesstatic
     */
    public function removeAttribute(string $attributeClass, int $target = \Attribute::TARGET_ALL): static
    {
        if ($target === \Attribute::TARGET_ALL || $target === AttributeType::ALL) {
            unset($this->registry[strtolower($attributeClass)]);
        } else {
            $this->registry[strtolower($attributeClass)][1] ^= $target;
        }

        return $this;
    }

    protected function runAttribute(\ReflectionAttribute $attribute, AttributeHandler $handler): AttributeHandler
    {
        /** @var callable|object $attrInstance */
        $attrInstance = $attribute->newInstance();

        $this->prepareAttribute($attribute);

        if (!is_callable($attrInstance)) {
            $class = get_class($attribute);
            throw new \LogicException("Attribute: {$class} is not invokable.");
        }

        $result = $attrInstance($handler);

        return $this->createHandler($result, $handler->getReflector(), $handler->getObject());
    }

    /**
     * createAttributeHandler
     *
     * @param  callable     $getter
     * @param  \Reflector   $reflector
     * @param  object|null  $object
     *
     * @return  AttributeHandler
     */
    protected function createHandler(callable $getter, \Reflector $reflector, ?object $object = null): AttributeHandler
    {
        return new AttributeHandler($getter, $reflector, $object, $this);
    }

    protected function prepareAttribute(object $attribute): void
    {
        //
    }

    protected static function normalizeClassName(string $className): string
    {
        return strtolower(trim($className, '\\'));
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

    public static function getFirstAttributeInstance(
        mixed $value,
        string $attributeClass,
    ): ?object {
        $attr = static::getFirstAttribute($value, $attributeClass);

        return $attr ? $attr->newInstance() : null;
    }

    public static function getFirstAttribute(
        mixed $value,
        string $attributeClass,
    ): ?\ReflectionAttribute {
        $attrs = static::getAttributesFromAny($value, $attributeClass);

        return $attrs ? $attrs[0] : null;
    }

    public static function runAttributeIfExists(
        mixed $valueOrAttrs,
        string $attributeClass,
        callable $handler
    ): int {
        $count = 0;

        if (!is_array($valueOrAttrs)) {
            $valueOrAttrs = (array) static::getAttributesFromAny($valueOrAttrs, $attributeClass);
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
}
