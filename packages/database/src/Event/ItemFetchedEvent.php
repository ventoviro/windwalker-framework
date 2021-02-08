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
    protected ?Collection $item;

    /**
     * @return Collection|null
     */
    public function getItem(): ?Collection
    {
        return $this->item;
    }

    /**
     * @param  Collection|null  $item
     *
     * @return  static  Return self to support chaining.
     */
    public function setItem(?Collection $item): static
    {
        $this->item = $item;

        return $this;
    }
}
