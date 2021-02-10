<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Windwalker\Data\Collection;
use Windwalker\Event\AbstractEvent;

/**
 * The ItemFetchedEvent class.
 */
class ItemFetchedEvent extends AbstractEvent
{
    protected ?object $item;

    /**
     * @return object|null
     */
    public function getItem(): ?object
    {
        return $this->item;
    }

    /**
     * @param  object|null  $item
     *
     * @return  static  Return self to support chaining.
     */
    public function setItem(?object $item): static
    {
        $this->item = $item;

        return $this;
    }
}
