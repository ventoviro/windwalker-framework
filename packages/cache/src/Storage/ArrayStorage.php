<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

/**
 * Runtime Storage.
 *
 * @since 2.0
 */
class ArrayStorage implements StorageInterface
{
    /**
     * Property storage.
     *
     * @var  array
     */
    protected array $data = [];

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        $data = $this->data[$key] ?? null;

        if ($data === null) {
            return null;
        }

        [$expiration, $value] = $data;

        if ($expiration !== 0 && time() > $expiration) {
            return null;
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        if (!isset($this->data[$key])) {
            return false;
        }

        [$expiration, $value] = $this->data[$key];

        return time() <= $expiration;
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $this->data = [];

        return true;
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        unset($this->data[$key]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(string $key, mixed $value, int $expiration = 0): bool
    {
        $this->data[$key] = [
            $expiration,
            $value,
        ];

        return true;
    }

    /**
     * Method to get property Data
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Method to set property data
     *
     * @param  array  $data
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
