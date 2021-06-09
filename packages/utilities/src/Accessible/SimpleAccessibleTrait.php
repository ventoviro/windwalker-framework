<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Accessible;

use Windwalker\Utilities\TypeCast;

/**
 * Trait SimpleAccessibleTrait
 */
trait SimpleAccessibleTrait
{
    /**
     * @var  array
     */
    protected mixed $storage = [];

    /**
     * Get value from this object.
     *
     * @param  mixed  $key
     *
     * @return  mixed
     */
    public function &get(mixed $key): mixed
    {
        $ret = null;

        if (!isset($this->getStorage()[$key])) {
            return $ret;
        }

        $ret =& $this->getStorage()[$key];

        return $ret;
    }

    /**
     * Set value to this object.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     *
     * @return  static
     */
    public function set(mixed $key, mixed $value): static
    {
        $this->getStorage()[$key] = $value;

        return $this;
    }

    /**
     * Set value default if not exists.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function def(mixed $key, mixed $default): mixed
    {
        $this->getStorage()[$key] = $this->getStorage()[$key] ?? $default;

        return $this->getStorage()[$key];
    }

    /**
     * Check a key exists or not.
     *
     * @param  mixed  $key
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function has(mixed $key): bool
    {
        return isset($this->getStorage()[$key]);
    }

    /**
     * remove
     *
     * @param  mixed  $key
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function remove(mixed $key): static
    {
        if ($this->has($key)) {
            unset($this->getStorage()[$key]);
        }

        return $this;
    }

    /**
     * Creates a copy of storage.
     *
     * @param  bool  $recursive
     *
     * @param  bool  $onlyDumpable
     *
     * @return array
     */
    public function dump(bool $recursive = false, bool $onlyDumpable = false): array
    {
        if (!$recursive) {
            return $this->getStorage();
        }

        $data = $this->getStorage();

        return TypeCast::toArray($data, true, $onlyDumpable);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->getStorage();
    }

    /**
     * count
     *
     * @return  int
     *
     * @since  __DEPLOY_VERSION__
     */
    public function count(): int
    {
        return count($this->getStorage());
    }

    /**
     * @return array|null
     */
    public function &getStorage(): ?array
    {
        return $this->storage;
    }
}
