<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event\Listener;

use Countable;
use IteratorAggregate;
use SplPriorityQueue;
use Windwalker\Utilities\Proxy\CallableProxy;

/**
 * Class ListenerPriorityQueue
 *
 * @since 2.0
 */
class ListenersQueue implements IteratorAggregate, Countable
{
    /**
     * @var ListenerCallable[]
     */
    protected array $queue = [];

    /**
     * Add a listener with the given priority only if not already present.
     *
     * @param  callable  $listener  The listener.
     * @param  ?int      $priority  The listener priority.
     *
     * @return  ListenersQueue  This method is chainable.
     *
     * @since   2.0
     */
    public function add(callable $listener, ?int $priority = null): static
    {
        $this->queue[] = [$listener, $priority ?? ListenerPriority::NORMAL];

        return $this;
    }

    /**
     * Remove a listener from the queue.
     *
     * @param  callable  $listener  The listener.
     *
     * @return  ListenersQueue  This method is chainable.
     *
     * @since   2.0
     */
    public function remove(callable $listener): static
    {
        $this->queue = array_values(
            array_filter(
                $this->queue,
                static function ($item) use ($listener) {
                    return !static::isCallableSame($item[0], $listener);
                }
            )
        );

        return $this;
    }

    /**
     * Tell if the listener exists in the queue.
     *
     * @param  callable  $listener  The listener.
     *
     * @return  boolean  True if it exists, false otherwise.
     *
     * @since   2.0
     */
    public function has(callable $listener): bool
    {
        foreach ($this->queue as $item) {
            if (static::isCallableSame($item[0], $listener)) {
                return true;
            }
        }

        return false;
    }

    /**
     * isCallableSame
     *
     * @param  callable  $inner
     * @param  callable  $outer
     *
     * @return  bool
     */
    private static function isCallableSame(callable $inner, callable $outer): bool
    {
        if ($inner === $outer) {
            return true;
        }

        if (!$inner instanceof CallableProxy) {
            return false;
        }

        return $inner->get(true) === $outer;
    }

    /**
     * Get all listeners contained in this queue, sorted according to their priority.
     *
     * @return  callable[]  An array of listeners.
     *
     * @since   2.0
     */
    public function getAll(): array
    {
        $listeners = [];

        foreach ($this as $listener) {
            $listeners[] = $listener[0];
        }

        return $listeners;
    }

    /**
     * Get the inner queue with its cursor on top of the heap.
     *
     * @return  SplPriorityQueue  The inner queue.
     *
     * @since   2.0
     */
    public function getIterator(): SplPriorityQueue
    {
        // SplPriorityQueue queue is a heap.
        $queue = new SplPriorityQueue();

        foreach ($this->queue as $item) {
            $queue->insert(...$item);
        }

        return $queue;
    }

    /**
     * Count the number of listeners in the queue.
     *
     * @return  integer  The number of listeners in the queue.
     *
     * @since   2.0
     */
    public function count(): int
    {
        return count($this->queue);
    }
}
