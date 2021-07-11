<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Event\Attributes;

use Attribute;
use Windwalker\Event\EventListenableInterface;
use Windwalker\Event\Listener\ListenerPriority;

use function Windwalker\disposable;

/**
 * The ListenTo class.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class ListenTo
{
    /**
     * ListenTo constructor.
     *
     * @param  string    $event
     * @param  int|null  $priority
     * @param  bool      $once
     */
    public function __construct(
        public string $event,
        public ?int $priority = ListenerPriority::NORMAL,
        public bool $once = false,
    ) {
        //
    }

    public function listen(EventListenableInterface $dispatcher, callable $listener): void
    {
        if ($this->once) {
            $listener = disposable($listener);
        }

        $dispatcher->on($this->event, $listener, $this->priority);
    }
}
