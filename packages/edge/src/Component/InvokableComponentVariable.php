<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Component;

use Closure;

/**
 * The InvokableComponentVariable class.
 */
class InvokableComponentVariable
{
    /**
     * InvokableComponentVariable constructor.
     *
     * @param  Closure  $callable
     */
    public function __construct(protected Closure $callable)
    {
    }

    /**
     * Dynamically proxy attribute access to the variable.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->__invoke()->{$key};
    }

    /**
     * Dynamically proxy method access to the variable.
     *
     * @param  string  $method
     * @param  array   $args
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return $this->__invoke()->{$method}(...$args);
    }

    /**
     * Resolve the variable.
     *
     * @return mixed
     */
    public function __invoke()
    {
        return ($this->callable)();
    }

    /**
     * Resolve the variable as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->__invoke();
    }
}
