<?php

/**
 * Part of unicorn project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Iterator;

use Windwalker\Data\Collection;

use function Windwalker\collect;

/**
 * The ResultIterator class.
 */
class ResultIterator extends \IteratorIterator implements \ArrayAccess
{
    protected ?array $result = null;

    public function all(): Collection
    {
        return collect($this->result ??= iterator_to_array($this));
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param  mixed  $offset  An offset to check for.
     *
     * @return bool true on success or false on failure.
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->all()[$offset]);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param  mixed  $offset  The offset to retrieve.
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset): mixed
    {
        return $this->all()[$offset];
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param  mixed  $offset  The offset to assign the value to.
     * @param  mixed  $value   The value to set.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException(static::class . ' do not support offset set, use all() to get all data.');
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param  mixed  $offset  The offset to unset.
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException(static::class . ' do not support offset unset, use all() to get all data.');
    }
}
