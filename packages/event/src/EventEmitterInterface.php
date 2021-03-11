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
 * The EventTriggerableInterface interface.
 *
 * @since  2.1.1
 */
interface EventEmitterInterface
{
    /**
     * Trigger an event.
     *
     * @param  object|string  $event  The event object or name.
     * @param  array          $args   The arguments to set in event.
     *
     * @return  EventInterface  The event after being passed through all listeners.
     *
     * @since   2.0
     */
    public function emit(object|string $event, array $args = []): object;
}
