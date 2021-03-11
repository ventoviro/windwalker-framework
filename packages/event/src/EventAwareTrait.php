<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event;

/**
 * Trait EventAwareTrait
 */
trait EventAwareTrait
{
    /**
     * Property dispatcher.
     *
     * @var  EventEmitter
     */
    protected ?EventEmitter $dispatcher = null;

    /**
     * Trigger an event.
     *
     * @param  object|string  $event  The event object or name.
     * @param  array          $args   The arguments to set in event.
     *
     * @return  EventInterface|object  The event after being passed through all listeners.
     *
     * @since   2.0
     */
    public function emit(object|string $event, array $args = []): object
    {
        return $this->getDispatcher()->emit($event, $args);
    }

    /**
     * Add a subscriber object with multiple listener methods to this dispatcher.
     * If object is not EventSubscriberInterface, it will be registered to all events matching it's methods name.
     *
     * @param  object|EventSubscriberInterface  $subscriber  The listener
     * @param  integer                          $priority    The listener priority.
     *
     * @return  static  This method is chainable.
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function subscribe(object $subscriber, ?int $priority = null): static
    {
        $this->getDispatcher()->subscribe($subscriber, $priority);

        return $this;
    }

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
    public function on(string $event, callable $callable, ?int $priority = null): static
    {
        $this->getDispatcher()->on($event, $callable, $priority);

        return $this;
    }

    /**
     * getDispatcher
     *
     * @return  EventEmitter
     */
    public function getDispatcher(): EventEmitter
    {
        if (!$this->dispatcher) {
            $this->dispatcher = new EventEmitter();
        }

        return $this->dispatcher;
    }
}
