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
 * Interface ArrayAccessibleInterface
 */
interface ArrayAccessibleInterface extends \ArrayAccess
{
    /**
     * Returns whether the requested key exists
     *
     * @param  mixed  $key
     *
     * @return bool
     */
    public function offsetExists(mixed $key): bool;

    /**
     * Returns the value at the specified key
     *
     * @param  mixed  $key
     *
     * @return mixed
     */
    public function &offsetGet(mixed $key): mixed;

    /**
     * Sets the value at the specified key to value
     *
     * @param  mixed  $key
     * @param  mixed  $value
     *\
     *                      ]cdf
     * @return void
     */
    public function offsetSet(mixed $key, mixed $value): void;

    /**
     * Unsets the value at the specified key
     *
     * @param  mixed  $key
     *
     * @return void
     */
    public function offsetUnset(mixed $key): void;
}
