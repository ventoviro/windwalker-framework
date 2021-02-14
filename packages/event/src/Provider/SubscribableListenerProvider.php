<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event\Provider;

use Windwalker\Attributes\AttributesAwareTrait;
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Event\Attributes\EventSubscriber;
use Windwalker\Event\Attributes\ListenTo;
use Windwalker\Event\EventInterface;
use Windwalker\Event\EventSubscriberInterface;
use Windwalker\Event\Listener\ListenerPriority;
use Windwalker\Event\Listener\ListenersQueue;
use Windwalker\Utilities\Assert\ArgumentsAssert;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\StrNormalise;

use function Windwalker\disposable;

/**
 * The SubscribableListenerProvider class.
 */
class SubscribableListenerProvider implements SubscribableListenerProviderInterface
{
    use AttributesAwareTrait;

    /**
     * @var ListenersQueue[]
     */
    protected array $queues = [];

    /**
     * SubscribableListenerProvider constructor.
     */
    public function __construct()
    {
        $this->configureAttributes($this->getAttributesResolver());
    }

    protected function configureAttributes(AttributesResolver $resolver): void
    {
        $resolver->registerAttribute(ListenTo::class, \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION);
        $resolver->setOption('provider', $this);
    }

    /**
     * @inheritDoc
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof EventInterface) {
            $eventName = $event->getName();
        } else {
            $eventName = get_class($event);
        }

        $eventName = strtolower($eventName);

        return $this->queues[$eventName] ?? new ListenersQueue();
    }

    /**
     * @inheritDoc
     */
    public function on(
        string $event,
        callable $listener,
        ?int $priority = ListenerPriority::NORMAL
    ): void {
        $event = strtolower($event);

        if (!isset($this->queues[$event])) {
            $this->queues[$event] = new ListenersQueue();
        }

        $this->queues[$event]->add($listener, $priority);
    }

    /**
     * @inheritDoc
     */
    public function subscribe(object $subscriber, ?int $priority = null): void
    {
        $hasAttribute = (bool) AttributesResolver::runAttributeIfExists(
            new \ReflectionObject($subscriber),
            EventSubscriber::class,
            disposable(fn (): object => $this->getAttributesResolver()->resolveMethods($subscriber))
        );

        if (!$hasAttribute) {
            $methods = get_class_methods($subscriber);

            foreach ($methods as $method) {
                $this->on(
                    $method,
                    [$subscriber, static::normalize($method)],
                    $priority
                );
            }
        }
    }

    /**
     * normalize
     *
     * @param  string  $methodName
     *
     * @return  string
     */
    private static function normalize(string $methodName): string
    {
        return lcfirst(StrNormalise::toCamelCase($methodName));
    }

    /**
     * Method to get property Listeners
     *
     * @return  ListenersQueue[]
     *
     * @since  __DEPLOY_VERSION__
     */
    public function &getQueues(): array
    {
        return $this->queues;
    }

    /**
     * isCallable
     *
     * @param  object           $subscriber
     * @param  string|callable  $methodName
     *
     * @return  bool
     */
    private static function isCallable(object $subscriber, mixed $methodName): bool
    {
        if (is_callable($methodName)) {
            return true;
        }

        if (is_string($methodName) && is_callable([$subscriber, $methodName])) {
            return true;
        }

        return false;
    }

    private static function toCallable(object $subscriber, $methodName): callable|array
    {
        if (is_callable($methodName)) {
            return $methodName;
        }

        if (is_string($methodName) && is_callable([$subscriber, $methodName])) {
            return [$subscriber, $methodName];
        }

        throw new \InvalidArgumentException(
            sprintf(
                'MethodName should be callable, %s got',
                TypeAssert::describeValue($methodName)
            )
        );
    }
}
