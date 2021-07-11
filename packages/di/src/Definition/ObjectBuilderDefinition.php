<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use BadMethodCallException;
use Closure;
use ReflectionException;
use Windwalker\DI\Container;
use Windwalker\DI\Exception\DependencyResolutionException;

/**
 * The ObjectBuilder class.
 *
 * @method object newInstance(string $class, array $args = [], int $options = 0)
 */
class ObjectBuilderDefinition implements DefinitionInterface
{
    /**
     * Property class.
     *
     * @var  string|callable
     */
    protected $class;

    /**
     * Property arguments.
     *
     * @var  array
     */
    protected array $arguments = [];

    /**
     * Property caches.
     *
     * @var  array
     */
    protected array $caches = [];

    /**
     * Property container.
     *
     * @var  ?Container
     */
    protected ?Container $container = null;

    /**
     * @var callable[]
     */
    protected array $extends = [];

    protected ?object $instance = null;

    public function fork(?array $args = null): static
    {
        $new = clone $this;

        if ($args !== null) {
            $new->addArguments($args);
        }

        return $new;
    }

    /**
     * ClassMeta constructor.
     *
     * @param  string|callable  $class
     * @param  Container|null   $container
     */
    public function __construct(callable|string $class, ?Container $container = null)
    {
        $this->container = $container;
        $this->class = $class;
    }

    /**
     * Resolve this definition.
     *
     * @param  Container  $container  The Container object.
     *
     * @return object
     * @throws ReflectionException
     */
    public function resolve(Container $container): object
    {
        $object = $container->newInstance(
            $this->getClass(),
            $this->getArguments()
        );

        foreach ($this->extends as $extend) {
            $object = $extend($object, $container);
        }

        return $object;
    }

    /**
     * Set new value or factory callback to this definition.
     *
     * @param  mixed  $value  Value or callable.
     *
     * @return  void
     */
    public function set(mixed $value): void
    {
        $this->class = $value;
    }

    /**
     * Method to get property Argument
     *
     * @param  string  $name
     * @param  mixed   $default
     *
     * @return array
     * @throws DependencyResolutionException
     * @throws ReflectionException
     */
    public function getArgument(mixed $name, $default = null): mixed
    {
        if (!isset($this->arguments[$name])) {
            return $default;
        }

        return $this->caches[$name] ??= $this->container->call($this->arguments[$name]);
    }

    /**
     * Method to set property argument
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  static Return self to support chaining.
     */
    public function setArgument(mixed $name, mixed $value): static
    {
        if (!$value instanceof Closure) {
            $value = fn() => $value;
        }

        $this->arguments[$name] = $value;
        unset($this->caches[$name]);

        return $this;
    }

    /**
     * hasArgument
     *
     * @param  string  $name
     *
     * @return  bool
     *
     * @since  3.5.1
     */
    public function hasArgument(string $name): bool
    {
        return isset($this->arguments[$name]);
    }

    /**
     * removeArgument
     *
     * @param  string  $name
     *
     * @return  static
     */
    public function removeArgument(string $name): static
    {
        unset($this->arguments[$name], $this->caches[$name]);

        return $this;
    }

    /**
     * Method to get property Arguments
     *
     * @return  array
     * @throws DependencyResolutionException
     * @throws ReflectionException
     */
    public function getArguments(): array
    {
        $args = [];

        foreach ($this->arguments as $name => $callable) {
            $args[$name] = $this->getArgument($name);
        }

        return $args;
    }

    /**
     * Method to set property arguments
     *
     * @param  array  $arguments
     *
     * @return  static  Return self to support chaining.
     */
    public function addArguments(array $arguments): static
    {
        foreach ($arguments as $name => $argument) {
            $this->setArgument($name, $argument);
        }

        return $this;
    }

    /**
     * reset
     *
     * @return  static
     */
    public function reset(): static
    {
        $this->arguments = [];
        $this->caches = [];
        $this->extends = [];

        return $this;
    }

    /**
     * extend
     *
     * @param  callable  $handler
     *
     * @return  $this
     *
     * @since  3.5.20
     */
    public function extend(callable $handler): static
    {
        $this->extends[] = $handler;

        return $this;
    }

    /**
     * clearExtends
     *
     * @return  $this
     *
     * @since  3.5.20
     */
    public function clearExtends(): static
    {
        $this->extends = [];

        return $this;
    }

    /**
     * __call
     *
     * @param  string  $name
     * @param  array   $args
     *
     * @return  mixed
     * @throws DependencyResolutionException
     * @throws ReflectionException
     */
    public function __call(string $name, array $args): mixed
    {
        $allowMethods = [
            'bind',
            'bindShared',
        ];

        if (in_array($name, $allowMethods, true)) {
            return $this->container->$name($this->class, ...$args);
        }

        $allowMethods = [
            'newInstance',
            'createObject',
            'createSharedObject',
        ];

        if (in_array($name, $allowMethods, true)) {
            $arguments = array_merge($this->getArguments(), $args[0] ?? []);

            $object = $this->container->$name($this->class, $arguments);

            foreach ($this->extends as $extend) {
                $object = $extend($this->container, $object);
            }

            return $object;
        }

        throw new BadMethodCallException(__METHOD__ . '::' . $name . '() not found.');
    }

    /**
     * Method to get property Container
     *
     * @return  Container
     *
     * @since  3.5.1
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Method to set property container
     *
     * @param  Container  $container
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.5.1
     */
    public function setContainer(Container $container): static
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Method to get property Class
     *
     * @return  string
     *
     * @since  3.5.19
     */
    public function getClass(): callable|string
    {
        return $this->class;
    }
}
