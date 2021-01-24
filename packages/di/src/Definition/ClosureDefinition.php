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
 * The CallbackDefinition class.
 */
class ClosureDefinition implements DefinitionInterface
{
    protected Closure $handler;

    /**
     * CallbackDefinition constructor.
     *
     * @param  Closure  $handler
     */
    public function __construct(Closure $handler)
    {
        $this->handler = $handler;
    }

    /**
     * resolve
     *
     * @param  Container  $container
     *
     * @return mixed
     */
    public function resolve(Container $container): mixed
    {
        return ($this->handler)($container);
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
        $this->handler = $value;
    }
}
