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
use Windwalker\DI\Exception\DefinitionException;

/**
 * The StoreDefinition class.
 */
class StoreDefinition extends DelegateDefinition implements StoreDefinitionInterface
{
    /**
     * @var mixed
     */
    protected $cache = null;

    protected int $options;

    /**
     * StoreDefinition constructor.
     *
     * @param  DefinitionInterface  $definition
     * @param  int                  $options
     */
    public function __construct(DefinitionInterface $definition, int $options)
    {
        $this->options = $options;

        parent::__construct($definition);
    }

    public function isShared(): bool
    {
        return (bool) ($this->options & Container::SHARED);
    }

    public function isProtected(): bool
    {
        return (bool) ($this->options & Container::PROTECTED);
    }

    public function reset(): void
    {
        $this->cache = null;
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
        if (!$this->isShared()) {
            $this->reset();
        }

        return $this->cache ??= parent::resolve($container);
    }

    /**
     * Set new value or factory callback to this definition.
     *
     * @param  mixed  $value  Value or callable.
     *
     * @return  void
     * @throws DefinitionException
     */
    public function set(mixed $value): void
    {
        if ($this->options & Container::PROTECTED) {
            throw new DefinitionException('This value / definition is protected.');
        }

        parent::set($value);
    }

    /**
     * extend
     *
     * @param  Closure  $closure
     *
     * @return  $this
     */
    public function extend(Closure $closure): static
    {
        $this->definition = new DelegateDefinition(
            $this->definition,
            $closure
        );

        return $this;
    }
}
