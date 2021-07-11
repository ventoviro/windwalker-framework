<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Queue\Queue;
use Windwalker\Queue\Worker;

/**
 * Trait QueueEventTrait
 */
trait QueueEventTrait
{
    protected Worker $worker;

    protected Queue $queue;

    /**
     * @return Worker
     */
    public function getWorker(): Worker
    {
        return $this->worker;
    }

    /**
     * @param  Worker  $worker
     *
     * @return  static  Return self to support chaining.
     */
    public function setWorker(Worker $worker): static
    {
        $this->worker = $worker;

        return $this;
    }

    /**
     * @return Queue
     */
    public function getQueue(): Queue
    {
        return $this->queue;
    }

    /**
     * @param  Queue  $queue
     *
     * @return  static  Return self to support chaining.
     */
    public function setQueue(Queue $queue): static
    {
        $this->queue = $queue;

        return $this;
    }
}
