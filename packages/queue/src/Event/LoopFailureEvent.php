<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\AbstractEvent;
use Windwalker\Queue\Worker;

/**
 * The WorkerLoopCycleFailure class.
 */
class LoopFailureEvent extends AbstractEvent
{
    protected Worker $worker;
    protected string $message;
    protected \Throwable $exception;

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
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param  string  $message
     *
     * @return  static  Return self to support chaining.
     */
    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return \Throwable
     */
    public function getException(): \Throwable
    {
        return $this->exception;
    }

    /**
     * @param  \Throwable  $exception
     *
     * @return  static  Return self to support chaining.
     */
    public function setException(\Throwable $exception): static
    {
        $this->exception = $exception;

        return $this;
    }
}
