<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Cache;

/**
 * The EdgeNullCache class.
 *
 * @since  3.0
 */
class EdgeArrayCache implements EdgeCacheInterface
{
    /**
     * Property data.
     *
     * @var  array
     */
    protected array $data = [];

    /**
     * isExpired
     *
     * @param  string  $path
     *
     * @return bool
     */
    public function isExpired(string $path): bool
    {
        return true;
    }

    /**
     * getCacheKey
     *
     * @param  string  $path
     *
     * @return  string
     */
    public function getCacheKey(string $path): string
    {
        return md5($path);
    }

    /**
     * get
     *
     * @param  string  $path
     *
     * @return  string
     */
    public function load(string $path): string
    {
        $key = $this->getCacheKey($path);

        return $this->data[$key] ?? '';
    }

    /**
     * store
     *
     * @param  string  $path
     * @param  string  $value
     *
     * @return void
     */
    public function store(string $path, string $value): void
    {
        $key = $this->getCacheKey($path);

        $this->data[$key] = $value;
    }

    /**
     * remove
     *
     * @param  string  $path
     *
     * @return void
     */
    public function remove(string $path): void
    {
        $key = $this->getCacheKey($path);

        unset($this->data[$key]);
    }

    /**
     * Method to get property Data
     *
     * @return  array
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
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
