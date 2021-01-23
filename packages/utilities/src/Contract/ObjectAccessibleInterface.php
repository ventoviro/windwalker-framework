<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Contract;

/**
 * Interface ObjectAccessibleInterface
 */
interface ObjectAccessibleInterface
{
    /**
     * Dynamically retrieve the value.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function &__get(mixed $key): mixed;

    /**
     * Dynamically set the value of an attribute.
     *
     * @param  string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function __set(mixed $key, mixed $value);

    /**
     * Dynamically check if an attribute is set.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function __isset(mixed $key): bool;

    /**
     * Dynamically unset an attribute.
     *
     * @param  string  $key
     *
     * @return void
     */
    public function __unset(mixed $key);
}
