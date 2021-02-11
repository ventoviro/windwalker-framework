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
use Windwalker\Query\Query;

/**
 * The BeforeFetchEvent class.
 */
class HydrateEvent extends AbstractEvent
{
    protected array|object|null $item;

    protected object|string $class;

    protected string|Query $query;

    /**
     * @return array|object|null
     */
    public function getItem(): array|object|null
    {
        return $this->item;
    }

    /**
     * @param  array|object|null  $item
     *
     * @return  static  Return self to support chaining.
     */
    public function setItem(array|object|null $item): static
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @return string|Query
     */
    public function getQuery(): Query|string
    {
        return $this->query;
    }

    /**
     * @param  string|Query  $query
     *
     * @return  static  Return self to support chaining.
     */
    public function setQuery(Query|string $query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return object|string
     */
    public function getClass(): object|string
    {
        return $this->class;
    }

    /**
     * @param  object|string  $class
     *
     * @return  static  Return self to support chaining.
     */
    public function setClass(object|string $class): static
    {
        $this->class = $class;

        return $this;
    }
}
