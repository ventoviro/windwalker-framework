<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Test\Traits;

use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The TestAccessorTrait class.
 */
trait TestAccessorTrait
{
    /**
     * getValue
     *
     * @param  mixed   $obj
     * @param  string  $name
     *
     * @return  mixed
     *
     * @throws \ReflectionException
     */
    public function getValue(object $obj, string $name): mixed
    {
        return ReflectAccessor::getValue($obj, $name);
    }

    /**
     * setValue
     *
     * @param  object  $obj
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  void
     *
     * @throws \ReflectionException
     */
    public function setValue(object $obj, string $name, mixed $value): void
    {
        ReflectAccessor::setValue($obj, $name, $value);
    }

    /**
     * invoke
     *
     * @param  object  $obj
     * @param  string  $method
     * @param  mixed   ...$args
     *
     * @return  mixed
     *
     * @throws \ReflectionException
     */
    public function invoke(object $obj, string $method, ...$args): mixed
    {
        return ReflectAccessor::invoke($obj, $method, ...$args);
    }
}
