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
use Windwalker\Event\Event;
use Windwalker\Event\EventInterface;
use Windwalker\Event\Listener\ListenerCallable;
use Windwalker\Event\Listener\ListenerPriority;
use Windwalker\Event\Listener\ListenersQueue;
use Windwalker\Utilities\Proxy\CallableProxy;
use Windwalker\Utilities\Proxy\DisposableCallable;
use Windwalker\Utilities\Proxy\TimesLimitedCallable;

/**
 * The CompositeProvider class.
 */
class CompositeListenerProvider implements SubscribableListenerProviderInterface
{
    /**
     * @var SubscribableListenerProviderInterface
     */
    protected $mainProvider;

    /**
     * @var ListenerProviderInterface[]
     */
    protected $providers = [];

    /**
     * create
     *
     * @param  ListenerProviderInterface|null  $provider
     *
     * @return  static
     */
    public static function create(?ListenerProviderInterface $provider = null): self
    {
        if (!$provider instanceof static) {
            if ($provider instanceof SubscribableListenerProvider || $provider === null) {
                $provider = new CompositeListenerProvider($provider);
            } else {
                $provider = new CompositeListenerProvider(null, [$provider]);
            }
        }

        return $provider;
    }

    /**
     * CompositeListenerProvider constructor.
     *
     * @param  SubscribableListenerProvider  $mainProvider
     * @param  ListenerProviderInterface[]   $providers
     */
    public function __construct(SubscribableListenerProvider $mainProvider = null, array $providers = [])
    {
        $this->mainProvider = $mainProvider ?: new SubscribableListenerProvider();

        foreach ($providers as $provider) {
            $this->appendProvider($provider);
        }
    }

    /**
     * @inheritDoc
     */
    public function getListenersForEvent(object $event): iterable
    {
        foreach ($this->providerIterator() as $provider) {
            if ($provider instanceof SubscribableListenerProvider) {
                foreach ($provider->getListenersForEvent($event) as $listener) {
                    $this->removeIfNecessary($event, $listener);

                    yield $listener;
                }
            } else {
                yield from $provider->getListenersForEvent($event);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function on(
        string $event,
        callable $listener,
        ?int $priority = ListenerPriority::NORMAL
    ): void {
        $this->mainProvider->on($event, $listener, $priority);
    }

    /**
     * @inheritDoc
     */
    public function subscribe(object $subscriber, ?int $priority = null): void
    {
        $this->mainProvider->subscribe($subscriber, $priority);
    }

    /**
     * getQueues
     *
     * @return  ListenersQueue[]
     */
    private function &getQueues(): array
    {
        return $this->mainProvider->getQueues();
    }

    /**
     * removeIfNecessary
     *
     * @param  object    $event
     * @param  callable  $listener
     *
     * @return  void
     *
     * @throws \ReflectionException
     */
    private function removeIfNecessary(object $event, callable $listener): void
    {
        if ($listener instanceof DisposableCallable) {
            $this->off($event, $listener);

            return;
        }

        if ($listener instanceof TimesLimitedCallable && $listener->getLimits() <= ($listener->getCallTimes() + 1)) {
            $this->off($event, $listener);

            return;
        }
    }

    /**
     * off
     *
     * @param  mixed  $listenerOrSubscriber
     *
     * @return  static
     *
     * @throws \ReflectionException
     */
    public function remove(mixed $listenerOrSubscriber): static
    {
        if ($this->isSubscriber($listenerOrSubscriber)) {
            foreach ($this->getQueues() as $queue) {
                $this->offSubscriber($queue, $listenerOrSubscriber);
            }
        } else {
            foreach ($this->getQueues() as $queue) {
                $queue->remove($listenerOrSubscriber);
            }
        }

        return $this;
    }

    /**
     * offEvent
     *
     * @param  string|EventInterface  $event
     * @param  callable|object|null   $listenerOrSubscriber
     *
     * @return  static
     * @throws \ReflectionException
     */
    public function off(string|EventInterface $event, callable|object|null $listenerOrSubscriber = null): static
    {
        $event = Event::wrap($event);

        $listeners = &$this->getQueues();

        $eventName = strtolower($event->getName());

        if (!isset($listeners[$eventName])) {
            return $this;
        }

        if ($listenerOrSubscriber === null) {
            unset($listeners[$eventName]);
        } else {
            $queue = $listeners[$eventName];

            if ($this->isSubscriber($listenerOrSubscriber)) {
                $this->offSubscriber($queue, $listenerOrSubscriber);
            } else {
                $queue->remove($listenerOrSubscriber);
            }
        }

        return $this;
    }

    /**
     * offSubscriber
     *
     * @param  ListenersQueue  $queue
     * @param  object          $subscriber
     *
     * @return  void
     *
     * @throws \ReflectionException
     */
    private function offSubscriber(ListenersQueue $queue, object $subscriber): void
    {
        /** @var ListenerCallable $listener */
        foreach ($queue as $listener) {
            $callable = $listener;

            if ($callable instanceof \Closure) {
                $ref  = new \ReflectionFunction($callable);
                $that = $ref->getClosureThis();
            } elseif (is_array($callable)) {
                $that = $callable[0];
            } else {
                continue;
            }

            if ($that === $subscriber) {
                $queue->remove($listener);
            }
        }
    }

    /**
     * isSubscriber
     *
     * @param  mixed  $listener
     *
     * @return  bool
     */
    private function isSubscriber(mixed $listener): bool
    {
        return !is_array($listener)
            && !is_string($listener)
            && !$listener instanceof \Closure
            && !$listener instanceof CallableProxy;
    }

    /**
     * providerIterator
     *
     * @return  \Generator|ListenerProviderInterface[]
     */
    private function providerIterator(): \Generator
    {
        yield $this->mainProvider;

        foreach ($this->providers as $provider) {
            yield $provider;
        }
    }

    /**
     * appendProvider
     *
     * @param  ListenerProviderInterface  $provider
     *
     * @return  void
     */
    public function appendProvider(ListenerProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * resetProviders
     *
     * @return  void
     */
    public function resetProviders(): void
    {
        $this->providers = [];
    }
}
