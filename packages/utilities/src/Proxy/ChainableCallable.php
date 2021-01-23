<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Proxy;

/**
 * The Chainable Callable proxy. Usage:
 *
 * ```php
 * $fn = (new ChainableCallable(fn () => return $result))
 *     ->chain(fn ($result) => ...)
 *     ->chain(fn ($result) => ...)
 *     ->chain(fn ($result) => ...);
 *
 * $fn();
 * ```
 *
 * All chained callbacks will run after main callback and fetch previous callback
 * return value as argument.
 */
class ChainableCallable extends CallableProxy
{
    /**
     * @var callable[]
     */
    protected $queue = [];

    /**
     * @inheritDoc
     */
    public function __invoke(...$args)
    {
        $result = parent::__invoke($args);

        foreach ($this->queue as $callable) {
            $result = $callable($result);
        }

        return $result;
    }

    /**
     * chain
     *
     * @param  callable  $callable
     *
     * @return  static
     */
    public function chain(callable $callable): static
    {
        $this->queue[] = $callable;

        return $this;
    }

    /**
     * Method to get property Queue
     *
     * @return  array[callable]
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getQueue(): array
    {
        return $this->queue;
    }
}
