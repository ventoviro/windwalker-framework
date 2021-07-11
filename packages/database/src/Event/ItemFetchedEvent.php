<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The ItemFetchedEvent class.
 */
class ItemFetchedEvent extends AbstractEvent
{
    protected ?array $item;

    /**
     * @return object|null
     */
    public function getItem(): ?array
    {
        return $this->item;
    }

    /**
     * @param  array|null  $item
     *
     * @return  static  Return self to support chaining.
     */
    public function setItem(?array $item): static
    {
        $this->item = $item;

        return $this;
    }
}
