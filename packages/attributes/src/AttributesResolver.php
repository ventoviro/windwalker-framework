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
     * @param  object  $instance
     *
     * @return  AttributeHandler
     */
    public function resolveObjectDecorate(object $instance): AttributeHandler
    {
        $ref = ReflectAccessor::reflect($instance);
        $object = $ref instanceof \ReflectionObject ? $instance : null;

        // If is closure, get closure back.
        if ($instance instanceof \ReflectionFunction) {
            $object = $instance->getClosure();
            $instance = $object;
        }

        $builder = $this->createHandler(fn() => $instance, $ref);

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

    public function resolveCallable(callable $callable, ?object $context = null): callable
    {
        $ref     = new ReflectionCallable($callable);
        $funcRef = $ref->getReflector();

        $closure = $ref->getClosure();

        if ($context) {
            $closure = $closure->bindTo($context, $context);
        }

        $attributes = $funcRef->getAttributes();

        if (!$attributes) {
            return $closure;
        }

        $handler = $this->createHandler($closure, $ref, $ref->getObject());

        foreach ($attributes as $attribute) {
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
        $ref = ReflectAccessor::reflect($instance);
        $object = $ref instanceof \ReflectionObject ? $instance : null;

        /** @var \ReflectionProperty $property */
        foreach ($ref->getProperties() as $property) {
            if ($property->isPrivate() || $property->isProtected()) {
                $property->setAccessible(true);
            }

            $getter = fn() => $object && $property->isInitialized($object)
                ? $property->getValue($object)
                : $property->getDefaultValue();

            foreach ($property->getAttributes() as $attribute) {
                if ($this->hasAttribute($attribute, \Attribute::TARGET_PROPERTY)) {
                    $getter = $this->runAttribute($attribute, $this->createHandler($getter, $property, $object));
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
        $ref = ReflectAccessor::reflect($instance);
        $object = $ref instanceof \ReflectionObject ? $instance : null;
        $target = $ref instanceof \ReflectionObject ? $instance : $ref->getName();

        foreach ($ref->getMethods() as $method) {
            $getter = fn(): array => [$target, $method->getName()];

            foreach ($method->getAttributes() as $attribute) {
                if ($this->hasAttribute($attribute, \Attribute::TARGET_METHOD)) {
                    $getter = $this->runAttribute($attribute, $this->createHandler($getter, $method, $object));
                }
            }

            $getter();
        }

        return $instance;
    }

    public function resolveConstants(object $instance): object
    {
        $ref = ReflectAccessor::reflect($instance);
        $object = $ref instanceof \ReflectionObject ? $instance : null;

        /** @var \ReflectionClassConstant $constant */
        foreach ($ref->getReflectionConstants() as $constant) {
            $getter = fn(): array => [$object, $constant];

            foreach ($constant->getAttributes() as $attribute) {
                if ($this->hasAttribute($attribute, \Attribute::TARGET_METHOD)) {
                    $getter = $this->runAttribute($attribute, $this->createHandler($getter, $constant, $object));
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
     * @return  static
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
     * @return  static
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

    public function runAttribute(\ReflectionAttribute $attribute, AttributeHandler $handler): AttributeHandler
    {
        /** @var callable|object $attrInstance */
        $attrInstance = $attribute->newInstance();

        $this->prepareAttribute($attribute);

        if (!is_callable($attrInstance)) {
            $class = $attrInstance::class;
            throw new \LogicException("Attribute: {$class} is not invokable.");
        }

        $result = $attrInstance($handler);

        // Attribute ran, create new handler for next.
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
}
