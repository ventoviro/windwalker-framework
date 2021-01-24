<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

namespace Windwalker\Edge\Cache;

use Windwalker\Cache\Storage\StorageInterface;

/**
 * The EdgeStorageCache class.
 *
 * @since  __DEPLOY_VERSION__
 */
class EdgeStorageCache implements EdgeCacheInterface
{
    /**
     * @var StorageInterface
     */
    protected StorageInterface $cache;

    /**
     * @inheritDoc
     */
    public function __construct(StorageInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function isExpired(string $path): bool
    {
        return $this->cache->has($this->getCacheKey($path));
    }

    /**
     * @inheritDoc
     */
    public function getCacheKey(string $path): string
    {
        return md5($path);
    }

    /**
     * @inheritDoc
     */
    public function load(string $path): string
    {
        return $this->cache->get($this->getCacheKey($path));
    }

    /**
     * @inheritDoc
     */
    public function store(string $path, string $value): void
    {
        $this->cache->save($this->getCacheKey($path), $value);
    }

    /**
     * @inheritDoc
     */
    public function remove(string $path): void
    {
        $this->cache->remove($this->getCacheKey($path));
    }
}
