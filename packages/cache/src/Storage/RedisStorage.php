<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

use Redis;

/**
 * The RedisStorage class.
 */
class RedisStorage implements StorageInterface
{
    /**
     * Property defaultHost.
     *
     * @var  string
     */
    protected string $defaultHost = '127.0.0.1';

    /**
     * Property defaultPort.
     *
     * @var  int
     */
    protected int $defaultPort = 6379;

    /**
     * @var Redis
     */
    protected ?Redis $driver = null;

    /**
     * RedisStorage constructor.
     *
     * @param $driver
     */
    public function __construct(?Redis $driver = null)
    {
        $this->driver = $driver;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        $this->connect();

        $value = $this->driver->get($key);

        if ($value === false) {
            return null;
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        $this->connect();

        return (bool) $this->driver->exists($key);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $this->connect();

        return $this->driver->flushall();
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        $this->connect();

        $this->driver->del($key);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(string $key, mixed $value, int $expiration = 0): bool
    {
        $this->connect();

        if (!$this->driver->set($key, $value)) {
            return false;
        }

        if ($expiration !== 0) {
            $ttl = $expiration - time();

            $this->driver->expire($key, $ttl);
        }

        return true;
    }

    /**
     * connect
     *
     * @return  static
     */
    protected function connect(): static
    {
        // We want to only create the driver once.
        if (isset($this->driver)) {
            return $this;
        }

        $this->driver = new Redis();

        if (($this->defaultHost === 'localhost' || filter_var($this->defaultHost, FILTER_VALIDATE_IP))) {
            $this->driver->connect($this->defaultHost, $this->defaultPort);
        } else {
            $this->driver->connect($this->defaultHost, null);
        }

        return $this;
    }
}
