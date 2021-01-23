<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event\Provider;

use Psr\EventDispatcher\ListenerProviderInterface;
use Windwalker\Event\EventInterface;

/**
 * The SimpleListenerProvider class.
 */
class SimpleListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array
     */
    protected $listeners = [];

    /**
     * SimpleListenerProvider constructor.
     *
     * @param  array  $listeners
     */
    public function __construct(array $listeners)
    {
        $this->setListeners($listeners);
    }

    /**
     * @inheritDoc
     */
    public function getListenersForEvent(object $event): iterable
    {
        $isEventInterface = $event instanceof EventInterface;

        foreach ($this->listeners as $eventType => $listeners) {
            if ($isEventInterface && $eventType !== $event->getName()) {
                continue;
            }

            if (!$isEventInterface && !$event instanceof $eventType) {
                continue;
            }

            foreach ($listeners as $listener) {
                yield $listener;
            }

            break;
        }
    }

    /**
     * add
     *
     * @param  string    $event
     * @param  callable  $listener
     *
     * @return  static
     */
    public function add(string $event, callable $listener): static
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $listener;

        return $this;
    }

    /**
     * Method to get property Listeners
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }

    /**
     * Method to set property listeners
     *
     * @param  array  $listeners
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setListeners(array $listeners): static
    {
        foreach ($listeners as $event => $listenerQueue) {
            foreach ($listenerQueue as $i => $listener) {
                $this->add($event, $listener);
            }
        }

        $this->listeners = $listeners;

        return $this;
    }
}
