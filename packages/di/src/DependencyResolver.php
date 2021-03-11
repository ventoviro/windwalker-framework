<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionUnionType;
use TypeError;
use Windwalker\DI\Definition\DefinitionInterface;
use Windwalker\DI\Definition\ObjectBuilderDefinition;
use Windwalker\DI\Exception\DefinitionResolveException;
use Windwalker\DI\Exception\DependencyResolutionException;
use Windwalker\Utilities\Reflection\ReflectionCallable;
use Windwalker\Utilities\Wrapper\RawWrapper;
use Windwalker\Utilities\Wrapper\ValueReference;

use function Windwalker\collect;

/**
 * The ObjectFactory class.
 */
class DependencyResolver
{
    protected Container $container;

    /**
     * DependencyResolver constructor.
     *
     * @param  Container  $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function newInstance(mixed $class, array $args = [], int $options = 0): object
    {
        if ($class instanceof DefinitionInterface) {
            return $this->container->resolve($class);
        }

        $options |= $this->container->getOptions();

        if (is_string($class)) {
            $builder = fn(array $args, int $options) => $this->resolvePropertiesAttributes(
                $this->newInstanceByClassName($class, $args, $options),
                $options
            );

            if (!($options & Container::IGNORE_ATTRIBUTES)) {
                $builder = $this->container->getAttributesResolver()
                    ->resolveClassCreate($class, $builder);
            }

            $instance = $builder($args, $options);
        } elseif (is_callable($class)) {
            $instance = $this->container->call($class, $args, null, $options);

            if (!is_object($instance)) {
                throw new \UnexpectedValueException(
                    sprintf(
                        'Thr callback for creating instance must return an object, got %s.',
                        get_debug_type($instance)
                    )
                );
            }

            $instance = $this->resolvePropertiesAttributes($instance, $options);

            if (!($options & Container::IGNORE_ATTRIBUTES)) {
                $instance = $this->container->getAttributesResolver()
                    ->decorateObject($instance);
            }

            // If is definition object, means this callable is a factory, let's resolve definition.
            if ($instance instanceof DefinitionInterface) {
                $instance = $this->container->resolve($instance);
            }

            return $instance;
        } else {
            throw new InvalidArgumentException(
                'New instance must get first argument as class name, callable or DefinitionInterface object.'
            );
        }

        return $instance;
    }

    protected function resolvePropertiesAttributes(object $instance, int $options): object
    {
        if (!($options & Container::IGNORE_ATTRIBUTES)) {
            $instance = $this->container->getAttributesResolver()
                ->resolveProperties($instance);
        }

        return $instance;
    }

    public function newInstanceByClassName(string $class, array $args = [], int $options = 0): object
    {
        $reflection = new ReflectionClass($class);

        $constructor = $reflection->getConstructor();

        // If there are no parameters, just return a new object.
        if (null === $constructor) {
            return new $class();
        }

        try {
            $args = array_merge($this->container->whenCreating($class)->getArguments(), $args);

            $args = $this->getMethodArgs($constructor, $args, $options);
        } catch (DependencyResolutionException $e) {
            throw new DependencyResolutionException(
                $e->getMessage() . ' - Target class: ' . $class,
                $e->getCode(),
                $e
            );
        }

        try {
            // Create a callable for the dataStore
            return $reflection->newInstanceArgs($args);
        } catch (TypeError $e) {
            throw new DependencyResolutionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Build an array of constructor parameters.
     *
     * @param  ReflectionFunctionAbstract  $method  Method for which to build the argument array.
     * @param  array                       $args    The default args if class hint not provided.
     * @param  int                         $options
     *
     * @return array Array of arguments to pass to the method.
     *
     * @throws DependencyResolutionException
     * @throws ReflectionException
     * @since   2.0
     */
    protected function getMethodArgs(ReflectionFunctionAbstract $method, array $args = [], int $options = 0): array
    {
        $methodArgs = [];

        foreach ($method->getParameters() as $i => $param) {
            $dependencyVarName = $param->getName();

            // Prior (1): Handler ...$args
            if ($param->isVariadic()) {
                $trailing = [];

                foreach ($args as $key => $v) {
                    if (is_numeric($key)) {
                        $trailing[] = &$this->resolveParameterValue($v, $param);
                    }

                    unset($v);
                }

                $trailing   = array_slice($trailing, $i);
                $methodArgs = array_merge($methodArgs, $trailing);
                continue;
            }

            // Prior (2): Argument with numeric keys.
            if (array_key_exists($i, $args)) {
                $methodArgs[$dependencyVarName] = &$this->resolveParameterValue($args[$i], $param);
                continue;
            }

            // Prior (3): Argument with named keys.
            if (array_key_exists($dependencyVarName, $args)) {
                $methodArgs[$dependencyVarName] = &$this->resolveParameterValue($args[$dependencyVarName], $param);
                continue;
            }

            // Prior (4): Argument with numeric keys.
            $value = &$this->resolveParameterValue(
                $this->resolveParameterDependency($param, $args, $options),
                $param
            );

            if ($value !== null) {
                $methodArgs[$dependencyVarName] = &$value;

                unset($value);
                continue;
            }

            unset($value);

            if ($param->isOptional()) {
                // Finally, if there is a default parameter, use it.
                if ($param->isDefaultValueAvailable()) {
                    $methodArgs[$dependencyVarName] = $param->getDefaultValue();
                }

                continue;
            }

            throw new DependencyResolutionException(
                sprintf(
                    'Cannot resolve argument %s: $%s for %s%s()',
                    $i + 1,
                    $dependencyVarName,
                    $method instanceof ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '',
                    $method->getShortName()
                )
            );

            $methodArgs[$i] = null;
        }

        return $methodArgs;
    }

    public function &resolveParameterDependency(ReflectionParameter $param, array $args = [], int $options = 0)
    {
        $nope    = null;
        $options |= $this->container->getOptions();

        $autowire = $options & Container::AUTO_WIRE;

        $type              = $param->getType();
        $dependencyVarName = $param->getName();

        if (!$type) {
            return $nope;
        }

        if ($type instanceof ReflectionUnionType) {
            $dependencies = $type->getTypes();
        } else {
            $dependencies = [$type];
        }

        foreach ($dependencies as $type) {
            $depObject           = null;
            $dependencyClassName = $type->getName();

            if (!class_exists($dependencyClassName) && !interface_exists($dependencyClassName)) {
                // Next dependency
                continue;
            }

            $dependency = new ReflectionClass($dependencyClassName);

            // If the dependency class name is registered with this container or a parent, use it.
            if ($this->container->has($dependencyClassName)) {
                $depObject = $this->container->get($dependencyClassName);
            } elseif (array_key_exists($dependencyVarName, $args)) {
                // If an arg provided, use it.
                return $args[$dependencyVarName];
            } elseif (
                $autowire
                && !$dependency->isAbstract()
                && !$dependency->isInterface()
                && !$dependency->isTrait()
            ) {
                // Otherwise we create this object recursive

                // Find child args if set
                if (isset($args[$dependencyClassName]) && is_array($args[$dependencyClassName])) {
                    $childArgs = $args[$dependencyClassName];
                } else {
                    $childArgs = [];
                }

                $depObject = $this->newInstance($dependencyClassName, $childArgs, $options);
            }

            if ($depObject instanceof $dependencyClassName) {
                return $depObject;
            }
        }

        if (isset($depObject)) {
            $types = collect($dependencies)
                ->map(fn (\ReflectionType $dep) => $dep->getName())
                ->implode('|');

            throw new DefinitionResolveException(
                sprintf(
                    'Unable to resolve %s argument #%s type: %s with %s given',
                    $param->getDeclaringClass()->getName(),
                    $param->getPosition(),
                    (string) $types,
                    get_debug_type($depObject ?? null)
                )
            );
        }

        return $nope;
    }

    /**
     * resolveArgumentValue
     *
     * @param  mixed                $value
     * @param  ReflectionParameter  $param
     * @param  int                  $options
     *
     * @return mixed
     *
     * @since  3.5.1
     */
    public function &resolveParameterValue(mixed &$value, ReflectionParameter $param, int $options = 0): mixed
    {
        if ($value instanceof RawWrapper) {
            $value = $value();
        } else {
            if ($value instanceof ValueReference) {
                $v = $value($this->container->getParameters());

                if ($v === null && $this->container->getParent() instanceof Container) {
                    $v = $value($this->container->getParent()->getParameters());
                }

                $value = $v;
            }

            if ($value instanceof ObjectBuilderDefinition) {
                $value = $this->container->resolve($value);
            }
        }

        $options |= $this->container->getOptions();

        if (!($options & Container::IGNORE_ATTRIBUTES) && $param->getAttributes()) {
            $value = &$this->container->getAttributesResolver()
                ->resolveParameter($value, $param);
        }

        return $value;
    }

    /**
     * Execute a callable with dependencies.
     *
     * @param  callable     $callable
     * @param  array        $args
     * @param  object|null  $context
     * @param  int          $options
     *
     * @return mixed
     *
     * @throws ReflectionException
     */
    public function call(callable $callable, array $args = [], ?object $context = null, int $options = 0): mixed
    {
        $ref = new ReflectionCallable($callable);

        $options |= $this->container->getOptions();

        $closure = function (array $args, int $options) use ($context, $callable, $ref) {
            $args = $this->getMethodArgs($ref->getReflector(), $args, $options);

            if (!($options & Container::IGNORE_ATTRIBUTES)) {
                $callable = $this->container->getAttributesResolver()
                    ->resolveCallable($callable, $context);
            }

            return $callable(...$args);
        };

        return $closure($args, $options);
    }
}
