<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Scheduler;

/**
 * The AsyncHandler class.
 */
class ScheduleRunner implements SchedulerInterface
{
    /**
     * @var SchedulerInterface[]
     */
    protected array $schedulers = [];

    /**
     * @var static
     */
    protected static $instance;

    /**
     * getInstance
     *
     * @param  static|null  $instance
     *
     * @return  static
     */
    public static function getInstance(?self $instance = null): self
    {
        if ($instance) {
            static::$instance = $instance;
        }

        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * AsyncRunner constructor.
     *
     * @param  SchedulerInterface[]  $schedulers
     */
    public function __construct(array $schedulers = [])
    {
        $this->schedulers = $schedulers;
    }

    /**
     * run
     *
     * @param  callable  $callback
     *
     * @return  ScheduleCursor
     */
    public function schedule(callable $callback): ScheduleCursor
    {
        return $this->getAvailableScheduler()->schedule($callback);
    }

    /**
     * done
     *
     * @param  ScheduleCursor|null  $cursor
     *
     * @return  void
     */
    public function done(?ScheduleCursor $cursor): void
    {
        $this->getAvailableScheduler()->done($cursor);
    }

    /**
     * wait
     *
     * @param  ScheduleCursor  $cursor
     *
     * @return  void
     */
    public function wait(ScheduleCursor $cursor): void
    {
        $this->getAvailableScheduler()->wait($cursor);
    }

    /**
     * getAvailableHandler
     *
     * @return  SchedulerInterface
     */
    public function getAvailableScheduler(): SchedulerInterface
    {
        foreach ($this->getSchedulers() as $handler) {
            if ($handler::isSupported()) {
                return $handler;
            }
        }

        throw new \DomainException('No available async handlers');
    }

    /**
     * getHandlers
     *
     * @return  SchedulerInterface[]
     */
    public function getSchedulers(): array
    {
        if ($this->schedulers === []) {
            $this->schedulers = [
                new SwooleScheduler(),
                new DeferredScheduler(),
            ];
        }

        return $this->schedulers;
    }

    /**
     * Method to set property handlers
     *
     * @param  SchedulerInterface[]  $schedulers
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setSchedulers(array $schedulers): static
    {
        $this->schedulers = $schedulers;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function isSupported(): bool
    {
        return true;
    }
}
