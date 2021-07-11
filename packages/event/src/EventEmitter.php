<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event;

use DomainException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionException;
use Rx\Observable;
use Rx\ObserverInterface;
use WeakMap;
use Windwalker\Event\Provider\CompositeListenerProvider;
use Windwalker\Utilities\Classes\ObjectBuilderAwareTrait;

use function Windwalker\disposable;
use function Windwalker\tap;

/**
 * The AttachableEventDispatcher class.
 */
class EventEmitter extends EventDispatcher implements
    EventEmitterInterface,
    EventListenableInterface,
    EventDisposableInterface
{
    use ObjectBuilderAwareTrait;

    /**
     * @var CompositeListenerProvider
     */
    protected ListenerProviderInterface $provider;

    /**
     * @var EventDispatcherInterface[]
     */
    protected WeakMap $dealers;

    /**
     * EventEmitter constructor.
     *
     * @param  ListenerProviderInterface  $provider
     */
    public function __construct(ListenerProviderInterface $provider = null)
    {
        parent::__construct(CompositeListenerProvider::create($provider));

        $this->dealers = new WeakMap();
    }

    /**
     * @inheritDoc
     */
    public function dispatch(object $event): object
    {
        return tap(
            parent::dispatch($event),
            function () use ($event) {
                foreach ($this->dealers as $dealer) {
                    $dealer->dispatch($event);
                }
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function emit(object|string $event, array $args = []): object
    {
        if (is_string($event) || $event instanceof EventInterface) {
            $event = Event::wrap($event, $args);
        }

        $this->dispatch($event);

        return $event;
    }

    /**
     * @inheritDoc
     */
    public function subscribe(object $subscriber, ?int $priority = null): static
    {
        $this->provider->subscribe($subscriber, $priority);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function on(string $event, callable $callable, ?int $priority = null): static
    {
        $this->provider->on($event, $callable, $priority);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function once(string $event, callable $callable, ?int $priority = null): static
    {
        $this->on($event, disposable($callable), $priority);

        return $this;
    }

    /**
     * observe
     *
     * @param  string    $event
     * @param  int|null  $priority
     *
     * @return  Observable
     */
    public function observe(string $event, ?int $priority = null): Observable
    {
        if (!class_exists(Observable::class)) {
            throw new DomainException('Please install reactivex/rxphp to support Observable.');
        }

        return Observable::create(
            function (ObserverInterface $subscriber) use ($event, $priority) {
                $this->on(
                    $event,
                    static function (EventInterface $event) use ($subscriber) {
                        $subscriber->onNext($event);
                    },
                    $priority
                );
            }
        );
    }

    /**
     * off
     *
     * @param  callable|object  $listenerOrSubscriber
     *
     * @return  static
     *
     * @throws ReflectionException
     */
    public function remove(callable|object $listenerOrSubscriber): static
    {
        $this->provider->remove($listenerOrSubscriber);

        return $this;
    }

    /**
     * offEvent
     *
     * @param  string|EventInterface  $event
     * @param  callable|object        $listenerOrSubscriber
     *
     * @return  static
     * @throws ReflectionException
     */
    public function off(string|EventInterface $event, $listenerOrSubscriber = null): static
    {
        $this->provider->off($event, $listenerOrSubscriber);

        return $this;
    }

    /**
     * getListeners
     *
     * @param  string|EventInterface  $event
     *
     * @return  callable[]
     */
    public function getListeners(string|EventInterface $event): iterable
    {
        return $this->provider->getListenersForEvent(Event::wrap($event));
    }

    /**
     * appendProvider
     *
     * @param  ListenerProviderInterface  $provider
     *
     * @return  static
     */
    public function appendProvider(ListenerProviderInterface $provider): static
    {
        $this->provider->appendProvider($provider);

        return $this;
    }

    /**
     * registerDealer
     *
     * @param  EventDispatcherInterface  $dispatcher
     *
     * @return  static
     */
    public function addDealer(EventDispatcherInterface $dispatcher): static
    {
        $this->dealers[$dispatcher] = $dispatcher;

        return $this;
    }

    /**
     * resetDealers
     *
     * @return  static
     */
    public function resetDealers(): static
    {
        $this->dealers = new WeakMap();

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
        parent::__clone();

        $this->dealers = clone $this->dealers;
    }
}
