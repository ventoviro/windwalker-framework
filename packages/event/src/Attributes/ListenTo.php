<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Event\Attributes;

use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\Event\Listener\ListenerPriority;
use Windwalker\Event\Provider\SubscribableListenerProviderInterface;
use Windwalker\Utilities\Assert\Assert;

use function Windwalker\disposable;

/**
 * The ListenTo class.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION | \Attribute::IS_REPEATABLE)]
class ListenTo implements AttributeInterface
{
    /**
     * ListenTo constructor.
     *
     * @param  string    $event
     * @param  int|null  $priority
     * @param  bool      $once
     */
    public function __construct(
        protected string $event,
        protected ?int $priority = ListenerPriority::NORMAL,
        protected bool $once = false,
    ) {
        //
    }

    /**
     * @inheritDoc
     */
    public function __invoke(AttributeHandler $handler): callable
    {
        return function () use ($handler) {
            $resolver = $handler->getResolver();
            $provider = $resolver->getOption('provider');

            if (!$provider instanceof SubscribableListenerProviderInterface) {
                throw new \LogicException(
                    sprintf(
                        'Event Provider should be %s, %s given',
                        SubscribableListenerProviderInterface::class,
                        Assert::describeValue($provider)
                    )
                );
            }

            $listener = $handler();

            $this->listen($provider, $listener);

            return $listener;
        };
    }

    public function listen(SubscribableListenerProviderInterface $provider, callable $listener): void
    {
        if ($this->once) {
            $listener = disposable($listener);
        }

        $provider->on(
            $this->event,
            $listener,
            $this->priority
        );
    }
}
