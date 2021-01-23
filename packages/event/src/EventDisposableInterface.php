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
 * Interface ListenOnceInterface
 */
interface EventDisposableInterface
{
    /**
     * Add single listener but only run once.
     *
     * @param  string    $event
     * @param  callable  $callable
     * @param  int|null  $priority
     *
     * @return  static
     */
    public function once(string $event, callable $callable, ?int $priority = null): static;
}
