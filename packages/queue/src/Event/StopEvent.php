<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The StopEvent class.
 */
class StopEvent extends AbstractEvent
{
    use QueueEventTrait;

    protected string $reason;

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @param  string  $reason
     *
     * @return  static  Return self to support chaining.
     */
    public function setReason(string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }
}
