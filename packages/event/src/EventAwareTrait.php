<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event;

use Psr\EventDispatcher\EventDispatcherInterface;

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
     * @psalm-template T
     * @psalm-param T         $event
     * @psalm-return T
     *
     * @since          2.0
     */
    public function emit(object|string $event, array $args = []): object
    {
        return $this->getEventDispatcher()->emit($event, $args);
    }

    /**
     * Add a subscriber object with multiple listener methods to this dispatcher.
     * If object is not EventSubscriberInterface, it will be registered to all events matching it's methods name.
     *
     * @param  object    $subscriber  The listener
     * @param  int|null  $priority    The listener priority.
     *
     * @return  static  This method is chainable.
     *
     * @since   2.0
     */
    public function subscribe(object $subscriber, ?int $priority = null): static
    {
        $this->getEventDispatcher()->subscribe($subscriber, $priority);

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
        $this->getEventDispatcher()->on($event, $callable, $priority);

        return $this;
    }

    /**
     * getDispatcher
     *
     * @return  EventEmitter
     */
    public function getEventDispatcher(): EventEmitter
    {
        return $this->dispatcher ??= new EventEmitter();
    }

    public function addEventDealer(EventDispatcherInterface|DispatcherAwareInterface $dispatcher): static
    {
        if ($dispatcher instanceof DispatcherAwareInterface) {
            $dispatcher = $dispatcher->getEventDispatcher();
        }

        $this->getEventDispatcher()->addDealer($dispatcher);

        return $this;
    }
}
