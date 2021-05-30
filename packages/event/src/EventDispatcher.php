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
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * The EventDispatcher class.
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var ListenerProviderInterface
     */
    protected ListenerProviderInterface $provider;

    /**
     * EventDispatcher constructor.
     *
     * @param  ListenerProviderInterface  $provider
     */
    public function __construct(ListenerProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @inheritDoc
     */
    public function dispatch(object $event): object
    {
        $stoppable = $event instanceof StoppableEventInterface;

        if ($stoppable && $event->isPropagationStopped()) {
            return $event;
        }

        foreach ($this->getProvider()->getListenersForEvent($event) as $listener) {
            $this->invokeListener($event, $listener);

            $stoppable = $event instanceof StoppableEventInterface;

            if ($stoppable && $event->isPropagationStopped()) {
                return $event;
            }
        }

        return $event;
    }

    /**
     * Invoke listener.
     *
     * @param  object    $event
     * @param  callable  $listener
     *
     * @return  void
     */
    protected function invokeListener(object $event, callable $listener): void
    {
        $listener($event);
    }

    /**
     * Method to get property Provider
     *
     * @return  ListenerProviderInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getProvider(): ListenerProviderInterface
    {
        return $this->provider;
    }

    /**
     * Method to set property provider
     *
     * @param  ListenerProviderInterface  $provider
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setProvider(ListenerProviderInterface $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * When an object is cloned, PHP 5 will perform a shallow copy of all of the object's properties.
     * Any properties that are references to other variables, will remain references.
     * Once the cloning is complete, if a __clone() method is defined,
     * then the newly created object's __clone() method will be called, to allow any necessary properties that need to
     * be changed. NOT CALLABLE DIRECTLY.
     *
     * @return void
     * @link https://php.net/manual/en/language.oop5.cloning.php
     */
    public function __clone(): void
    {
        $this->provider = clone $this->provider;
    }
}
