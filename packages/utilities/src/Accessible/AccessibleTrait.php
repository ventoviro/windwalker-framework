<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Accessible;

use Generator;
use Windwalker\Utilities\Contract\AccessibleInterface;
use Windwalker\Utilities\Contract\NullableInterface;

/**
 * The Accessible trait which implements AccessibleInterface.
 *
 * @see    AccessibleInterface
 * @see    NullableInterface
 *
 * @since  __DEPLOY_VERSION__
 */
trait AccessibleTrait
{
    use SimpleAccessibleTrait;

    /**
     * reset
     *
     * @param  array  $storage
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function reset(array $storage = []): static
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Returns whether the requested key exists
     *
     * @param  mixed  $key
     *
     * @return bool
     */
    public function offsetExists(mixed $key): bool
    {
        return $this->has($key);
    }

    /**
     * Returns the value at the specified key
     *
     * @param  mixed  $key
     *
     * @return mixed
     */
    public function &offsetGet(mixed $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Sets the value at the specified key to value
     *
     * @param  mixed  $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        if ($key === null || $key === '') {
            $this->storage[] = $value;

            return;
        }

        $this->set($key, $value);
    }

    /**
     * Unsets the value at the specified key
     *
     * @param  mixed  $key
     *
     * @return void
     */
    public function offsetUnset(mixed $key): void
    {
        $this->remove($key);
    }

    /**
     * Dynamically retrieve the value.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function &__get(mixed $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute.
     *
     * @param  string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function __set(mixed $key, mixed $value)
    {
        $this->set($key, $value);
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function __isset(mixed $key): bool
    {
        return $this->has($key);
    }

    /**
     * Dynamically unset an attribute.
     *
     * @param  string  $key
     *
     * @return void
     */
    public function __unset(mixed $key)
    {
        $this->remove($key);
    }

    /**
     * Get storage iterator.
     *
     * @return  Generator
     *
     * @since  __DEPLOY_VERSION__
     */
    public function &getIterator(): Generator
    {
        foreach ($this->storage as $key => &$value) {
            yield $key => $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isNull(): bool
    {
        return $this->storage === [];
    }

    /**
     * {@inheritDoc}
     */
    public function notNull(): bool
    {
        return !$this->isNull();
    }
}
