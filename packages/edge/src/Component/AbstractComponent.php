<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Component;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

use function Windwalker\collect;

/**
 * The AbstractComponent class.
 */
abstract class AbstractComponent
{
    /**
     * The cache of public property names, keyed by class.
     *
     * @var array
     */
    protected static array $propertyCache = [];

    /**
     * The cache of public method names, keyed by class.
     *
     * @var array
     */
    protected static array $methodCache = [];

    /**
     * The properties / methods that should not be exposed to the component.
     *
     * @var array
     */
    protected array $except = [];

    /**
     * The component alias name.
     *
     * @var string
     */
    public string $componentName = '';

    /**
     * The component attributes.
     *
     * @var ComponentAttributes
     */
    public ?ComponentAttributes $attributes = null;

    /**
     * Get the view / view contents that represent the component.
     *
     * @return Closure|string
     */
    abstract public function render(): Closure|string;

    /**
     * Resolve the Blade view or view file that should be used when rendering the component.
     *
     * @return Closure|string
     */
    public function resolveView(): Closure|string
    {
        return $this->render();
    }

    /**
     * Get the data that should be supplied to the view.
     *
     * @return array
     * @author Brent Roose
     *
     * @author Freek Van der Herten
     */
    public function data(): array
    {
        $this->attributes ??= $this->newAttributeBag();

        return array_merge($this->extractPublicProperties(), $this->extractPublicMethods());
    }

    /**
     * Extract the public properties for the component.
     *
     * @return array
     */
    protected function extractPublicProperties(): array
    {
        $class = get_class($this);

        if (!isset(static::$propertyCache[$class])) {
            $reflection = new ReflectionClass($this);

            static::$propertyCache[$class] = collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
                ->reject(
                    function (ReflectionProperty $property) {
                        return $property->isStatic();
                    }
                )
                ->reject(
                    function (ReflectionProperty $property) {
                        return $this->shouldIgnore($property->getName());
                    }
                )
                ->map(
                    function (ReflectionProperty $property) {
                        return $property->getName();
                    }
                )->dump();
        }

        $values = [];

        foreach (static::$propertyCache[$class] as $property) {
            $values[$property] = $this->{$property};
        }

        return $values;
    }

    /**
     * Extract the public methods for the component.
     *
     * @return array
     */
    protected function extractPublicMethods(): array
    {
        $class = get_class($this);

        if (!isset(static::$methodCache[$class])) {
            $reflection = new ReflectionClass($this);

            static::$methodCache[$class] = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
                ->reject(
                    function (ReflectionMethod $method) {
                        return $this->shouldIgnore($method->getName());
                    }
                )
                ->map(
                    function (ReflectionMethod $method) {
                        return $method->getName();
                    }
                );
        }

        $values = [];

        foreach (static::$methodCache[$class] as $method) {
            $values[$method] = $this->createVariableFromMethod(new ReflectionMethod($this, $method));
        }

        return $values;
    }

    /**
     * Create a callable variable from the given method.
     *
     * @param  ReflectionMethod  $method
     *
     * @return mixed
     */
    protected function createVariableFromMethod(ReflectionMethod $method)
    {
        return $method->getNumberOfParameters() === 0
            ? $this->createInvokableVariable($method->getName())
            : Closure::fromCallable([$this, $method->getName()]);
    }

    /**
     * Create an invokable, toStringable variable for the given component method.
     *
     * @param  string  $method
     *
     * @return InvokableComponentVariable
     */
    protected function createInvokableVariable(string $method): InvokableComponentVariable
    {
        return new InvokableComponentVariable(
            function () use ($method) {
                return $this->{$method}();
            }
        );
    }

    /**
     * Determine if the given property / method should be ignored.
     *
     * @param  string  $name
     *
     * @return bool
     */
    protected function shouldIgnore(string $name): bool
    {
        return str_starts_with($name, '__') || in_array($name, $this->ignoredMethods(), true);
    }

    /**
     * Get the methods that should be ignored.
     *
     * @return array
     */
    protected function ignoredMethods(): array
    {
        return array_merge(
            [
                'data',
                'render',
                'resolveView',
                'shouldRender',
                'view',
                'withName',
                'withAttributes',
            ],
            $this->except
        );
    }

    /**
     * Set the component alias name.
     *
     * @param  string  $name
     *
     * @return $this
     */
    public function withName(string $name): static
    {
        $this->componentName = $name;

        return $this;
    }

    /**
     * Set the extra attributes that the component should make available.
     *
     * @param  array  $attributes
     *
     * @return $this
     */
    public function withAttributes(array $attributes, array|ComponentAttributes $binding = []): static
    {
        // if ($binding instanceof ComponentAttributes) {
        //     $binding = $binding->getAttributes();
        // }

        $this->attributes = $this->attributes ?: $this->newAttributeBag();

        $this->attributes->setAttributes($attributes);

        return $this;
    }

    /**
     * Get a new attribute bag instance.
     *
     * @param  array  $attributes
     *
     * @return ComponentAttributes
     */
    protected function newAttributeBag(array $attributes = []): ComponentAttributes
    {
        return new ComponentAttributes($attributes);
    }

    /**
     * Determine if the component should be rendered.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return true;
    }
}
