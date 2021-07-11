<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use Closure;
use Windwalker\DI\Container;

/**
 * The DelegateDefinition class.
 */
class DelegateDefinition implements DefinitionInterface
{
    protected DefinitionInterface $definition;

    protected ?Closure $factory = null;

    /**
     * DecoratorDefinition constructor.
     *
     * @param  DefinitionInterface  $definition
     * @param  Closure|null         $factory
     */
    public function __construct(DefinitionInterface $definition, ?Closure $factory = null)
    {
        $this->definition = $definition;
        $this->factory = $factory;
    }

    /**
     * Resolve this definition.
     *
     * @param  Container  $container  The Container object.
     *
     * @return mixed
     */
    public function resolve(Container $container): mixed
    {
        $handler = $this->factory ?? fn($value, Container $container) => $value;

        return $handler($this->definition->resolve($container), $container);
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
        $this->factory = null;

        $this->definition->set($value);
    }
}
