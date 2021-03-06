<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

/**
 * The ArrayCookie class.
 */
class ArrayCookies extends Cookies
{
    protected array $storage = [];

    public static function create(array $storage = []): static
    {
        return new static($storage);
    }

    /**
     * ArrayCookies constructor.
     *
     * @param  array  $storage
     */
    public function __construct(array $storage = [])
    {
        $this->storage = $storage;
    }

    /**
     * set
     *
     * @param  string  $name
     * @param  string  $value
     *
     * @return  bool
     */
    public function set(string $name, string $value): bool
    {
        $this->storage[$name] = $value;

        return true;
    }

    /**
     * get
     *
     * @param  string  $name
     *
     * @return  string|null
     */
    public function get(string $name): ?string
    {
        return $this->storage[$name] ?? null;
    }

    public function remove(string $name): bool
    {
        $this->storage[$name] = '';

        return true;
    }

    /**
     * @return array
     */
    public function getStorage(): array
    {
        return $this->storage;
    }

    /**
     * @param  array  $storage
     *
     * @return  static  Return self to support chaining.
     */
    public function setStorage(array $storage): static
    {
        $this->storage = $storage;

        return $this;
    }
}
