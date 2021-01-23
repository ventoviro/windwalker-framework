<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use Windwalker\DI\Container;

/**
 * The DelegateDefinition class.
 */
class DelegateDefinition implements DefinitionInterface
{
    protected DefinitionInterface $definition;

    protected ?\Closure $handler = null;

    /**
     * DecoratorDefinition constructor.
     *
     * @param  DefinitionInterface  $definition
     * @param  \Closure|null        $handler
     */
    public function __construct(DefinitionInterface $definition, ?\Closure $handler = null)
    {
        $this->definition = $definition;
        $this->handler = $handler;
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
        $handler = $this->handler ?? fn ($value, Container $container) => $value;

        return $handler($this->definition->resolve($container), $container);
    }

    /**
     * Set new value or factory callback to this definition.
     *
     * @param  mixed  $value  Value or callable.
     *
     * @return  void
     */
    public function set($value): void
    {
        $this->definition->set($value);
    }
}
