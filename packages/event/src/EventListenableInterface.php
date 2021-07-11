<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event;

use InvalidArgumentException;

/**
 * Interface DispatcherInterface
 */
interface EventListenableInterface
{
    /**
     * Add a subscriber object with multiple listener methods to this dispatcher.
     * If object is not EventSubscriberInterface, it will be registered to all events matching it's methods name.
     *
     * @param  object    $subscriber  The listener
     * @param  int|null  $priority    The listener priority.
     *
     * @return  static  This method is chainable.
     *
     * @throws InvalidArgumentException
     * @since   2.0
     */
    public function subscribe(object $subscriber, ?int $priority = null): static;

    /**
     * Add single listener.
     *
     * @param  string    $event
     * @param  callable  $callable
     * @param  int       $priority
     *
     * @return  static
     *
     * @since   3.0
     */
    public function on(string $event, callable $callable, ?int $priority = null): static;
}
