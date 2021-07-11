<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache;

use DateInterval;
use Generator;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Throwable;
use Traversable;
use Windwalker\Cache\Exception\InvalidArgumentException;
use Windwalker\Cache\Exception\RuntimeException;
use Windwalker\Cache\Serializer\RawSerializer;
use Windwalker\Cache\Serializer\SerializerInterface;
use Windwalker\Cache\Storage\ArrayStorage;
use Windwalker\Cache\Storage\StorageInterface;
use Windwalker\Utilities\Assert\ArgumentsAssert;

/**
 * The Pool class.
 */
class CachePool implements CacheItemPoolInterface, CacheInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var bool
     */
    protected bool $commiting = false;

    /**
     * @var array
     */
    protected array $deferredItems = [];

    /**
     * @var StorageInterface
     */
    protected StorageInterface $storage;

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var bool
     */
    private bool $autoCommit = true;

    public function __construct(
        ?StorageInterface $storage = null,
        ?SerializerInterface $serializer = null,
        ?LoggerInterface $logger = null
    ) {
        $this->storage = $storage ?? new ArrayStorage();
        $this->serializer = $serializer ?? new RawSerializer();

        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @inheritDoc
     */
    public function getItem(string $key): CacheItemInterface|CacheItem
    {
        $item = new CacheItem($key);
        $item->setLogger($this->logger);

        if (!$this->storage->has($key)) {
            return $item;
        }

        $item->set($this->storage->get($key));

        return $item;
    }

    /**
     * @inheritDoc
     *
     * @return Traversable|CacheItemInterface[]
     */
    public function getItems(iterable $keys = []): Traversable|array|Generator
    {
        foreach ($keys as $key) {
            yield $key => $this->getItem($key);
        }
    }

    /**
     * @inheritDoc
     */
    public function hasItem(string $key): bool
    {
        return $this->getItem($key)->isHit();
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        try {
            $this->storage->clear();

            return true;
        } catch (RuntimeException $e) {
            $this->logException(
                'Clearing cache pool caused exception.',
                $e
            );

            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteItem(string $key): bool
    {
        try {
            $this->storage->remove($key);

            return true;
        } catch (RuntimeException $e) {
            $this->logException(
                'Deleting cache item caused exception.',
                $e
            );

            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        $results = true;

        foreach ($keys as $key) {
            $results = $this->deleteItem($key) && $results;
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        try {
            if (!$item instanceof CacheItem) {
                throw new InvalidArgumentException('Only support ' . CacheItem::class);
            }

            $expiration = $item->getExpiration()->getTimestamp();

            if ($expiration < time()) {
                $this->deleteItem($item->getKey());
                $item->setIsHit(false);

                return false;
            }

            $this->storage->save(
                $item->getKey(),
                $this->serializer->serialize($item->get()),
                $expiration
            );

            return true;
        } catch (RuntimeException $e) {
            $this->logException(
                'Saving cache item caused exception.',
                $e,
                $item
            );

            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        if (!$item instanceof CacheItem) {
            throw new InvalidArgumentException('Only support ' . CacheItem::class);
        }

        while ($this->commiting) {
            usleep(1);
        }

        $this->deferredItems[$item->getKey()] = $item;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        $this->commiting = true;

        foreach ($this->deferredItems as $key => $item) {
            if ($this->save($item)) {
                unset($this->deferredItems[$key]);
            }
        }

        $this->commiting = false;

        return !count($this->deferredItems);
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $item = $this->getItem($key);

        if (!$item->isHit()) {
            return $default;
        }

        return $this->serializer->unserialize($item->get());
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        $item = $this->getItem($key);

        $item->expiresAfter($ttl);
        $item->set($value);

        return $this->save($item);
    }

    /**
     * @inheritDoc
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete($key): bool
    {
        return $this->deleteItem($key);
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null): iterable
    {
        foreach ($this->getItems($keys) as $item) {
            yield $item->get();
        }
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null): bool
    {
        ArgumentsAssert::assert(
            is_iterable($values),
            '{caller} values must be iterable, %s given.',
            $values
        );

        $results = true;

        foreach ($values as $key => $value) {
            $results = $this->set($key, $value, $ttl) && $results;
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys): bool
    {
        ArgumentsAssert::assert(
            is_iterable($keys),
            '{caller} keys must be iterable, %s given.',
            $keys
        );

        $results = true;

        foreach ($keys as $key) {
            $results = $this->delete($key) && $results;
        }

        return $results;
    }

    /**
     * @inheritDoc
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function has($key): bool
    {
        return $this->hasItem($key);
    }

    /**
     * call
     *
     * @param  string                 $key
     * @param  callable               $handler
     * @param  null|int|DateInterval  $ttl
     *
     * @return  mixed
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function call(string $key, callable $handler, $ttl = null): mixed
    {
        $item = $this->getItem($key);

        if ($item->isHit()) {
            return $this->serializer->unserialize($item->get());
        }

        $item->set($data = $handler());
        $item->expiresAfter($ttl);

        $this->save($item);

        return $data;
    }

    /**
     * logException
     *
     * @param  string                   $message
     * @param  Throwable                $e
     * @param  CacheItemInterface|null  $item
     *
     * @return  void
     */
    protected function logException(string $message, Throwable $e, ?CacheItemInterface $item = null): void
    {
        $this->logger->critical(
            $message,
            [
                'exception' => $e,
                'key' => $item ? $item->getKey() : null,
            ]
        );
    }

    /**
     * Method to get property Storage
     *
     * @return  StorageInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * Method to set property storage
     *
     * @param  StorageInterface  $storage
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setStorage(StorageInterface $storage): static
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Method to get property Serializer
     *
     * @return  SerializerInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * Method to set property serializer
     *
     * @param  SerializerInterface  $serializer
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setSerializer(SerializerInterface $serializer): static
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * Method to get property DeferredItems
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getDeferredItems(): array
    {
        return $this->deferredItems;
    }

    /**
     * Method to set property autoCommit
     *
     * @param  bool  $autoCommit
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function autoCommit(bool $autoCommit): static
    {
        $this->autoCommit = $autoCommit;

        return $this;
    }

    /**
     * Commit when destructing.
     */
    public function __destruct()
    {
        if ($this->autoCommit) {
            $this->commit();
        }
    }
}
