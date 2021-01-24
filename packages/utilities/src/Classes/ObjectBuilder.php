<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

use Closure;
use ReflectionException;

/**
 * The ObjectResolver class.
 */
class ObjectBuilder
{
    protected ?Closure $builder = null;

    /**
     * Create object by class and resolve attributes.
     *
     * @param  string  $class
     * @param  mixed   ...$args
     *
     * @return  object
     * @throws ReflectionException
     */
    public function createObject(string $class, ...$args): object
    {
        return $this->getBuilder()($class, ...$args);
    }

    /**
     * @return Closure
     */
    public function getBuilder(): Closure
    {
        return $this->builder ??= function (string $class, ...$args) {
            return new $class(...$args);
        };
    }

    /**
     * @param  Closure  $builder
     *
     * @return  static  Return self to support chaining.
     */
    public function setBuilder(Closure $builder): static
    {
        $this->builder = $builder;

        return $this;
    }
}
