<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool;

use Windwalker\Pool\Exception\ConnectionPoolException;

/**
 * The AbstractConnection class.
 */
abstract class AbstractConnection implements ConnectionInterface
{
    protected int $id = 0;

    protected bool $active = false;

    protected int $lastTime = 0;

    protected ?PoolInterface $pool;

    /**
     * Set this to TRUE, if a connection not released back to pool but destructed, will throw an exception.
     *
     * @var bool
     */
    public bool $leakProtect = true;

    /**
     * @inheritDoc
     */
    public function setPool(?PoolInterface $pool): void
    {
        $this->pool = $pool;

        if ($pool !== null) {
            $this->id = $this->pool->getSerial();
        }
    }

    /**
     * @inheritDoc
     */
    public function reconnect(): mixed
    {
        $this->disconnect();

        return $this->connect();
    }

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function release(bool $force = false): void
    {
        if ($this->pool === null) {
            throw new ConnectionPoolException('No assigned pool of this connection.');
        }

        if ($this->active || $force) {
            $this->pool->release($this);
        }
    }

    /**
     * @inheritDoc
     */
    public function getLastTime(): int
    {
        return $this->lastTime;
    }

    /**
     * @inheritDoc
     */
    public function updateLastTime(): void
    {
        $this->lastTime = time();
    }

    /**
     * @inheritDoc
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * Is connection active.
     *
     * @return  bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    public function __destruct()
    {
        if ($this->active && $this->pool && $this->leakProtect) {
            trigger_error(
                sprintf(
                    'Connection ID: %s in pool: %s was not released but destruct.',
                    $this->getId(),
                    $this->pool::class
                ),
                E_USER_WARNING
            );
        }
    }
}
