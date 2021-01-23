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
 * The FileCacheHandler class.
 *
 * @since  3.0
 */
class EdgeFileCache implements EdgeCacheInterface
{
    /**
     * Property path.
     *
     * @var  string
     */
    protected string $path = '';

    /**
     * FileCacheHandler constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * isExpired
     *
     * @param  string  $path
     *
     * @return bool
     */
    public function isExpired(string $path): bool
    {
        $cachePath = $this->getCacheFile($this->getCacheKey($path));

        if (!is_file($cachePath)) {
            return true;
        }

        return filemtime($path) >= filemtime($cachePath);
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
        return md5(realpath($path));
    }

    /**
     * getCacheFile
     *
     * @param   string $key
     *
     * @return  string
     */
    public function getCacheFile(string $key): string
    {
        return $this->path . '/~' . $key;
    }

    /**
     * load
     *
     * @param  string  $path
     *
     * @return  string
     */
    public function load(string $path): string
    {
        return file_get_contents($this->getCacheFile($this->getCacheKey($path)));
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
        $value = "<?php /* File: {$path} */ ?>" . $value;

        $file = $this->getCacheFile($this->getCacheKey($path));

        if (!is_dir(dirname($file))) {
            if (!mkdir($concurrentDirectory = dirname($file), 0755, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        file_put_contents($file, $value);
    }

    /**
     * Remove an item from the cache by its unique key
     *
     * @param  string  $path  The path to remove.
     *
     * @return void
     */
    public function remove(string $path): void
    {
        @unlink($this->getCacheFile($this->getCacheKey($path)));
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param  string  $path
     *
     * @return  static  Return self to support chaining.
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }
}
