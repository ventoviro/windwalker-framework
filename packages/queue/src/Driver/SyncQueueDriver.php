<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use Windwalker\Queue\Job\JobInterface;
use Windwalker\Queue\QueueMessage;

/**
 * The SyncQueueDriver class.
 *
 * @since  3.2
 */
class SyncQueueDriver implements QueueDriverInterface
{
    /**
     * push
     *
     * @param  QueueMessage  $message
     *
     * @return int|string
     */
    public function push(QueueMessage $message): int|string
    {
        $job = $message->getSerializedJob();
        /** @var JobInterface $job */
        $job = unserialize($job);

        $this->runJob($job);

        return 0;
    }

    protected function runJob(callable $job)
    {
        return $job();
    }

    /**
     * pop
     *
     * @param  string|null  $channel
     *
     * @return QueueMessage|null
     */
    public function pop(?string $channel = null): ?QueueMessage
    {
        return new QueueMessage();
    }

    /**
     * delete
     *
     * @param  QueueMessage  $message
     *
     * @return SyncQueueDriver
     */
    public function delete(QueueMessage $message): static
    {
        return $this;
    }

    /**
     * release
     *
     * @param QueueMessage $message
     *
     * @return static
     */
    public function release(QueueMessage $message): static
    {
        return $this;
    }
}
