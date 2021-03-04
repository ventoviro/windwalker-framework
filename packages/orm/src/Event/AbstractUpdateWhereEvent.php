<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The AbstractUpdateBatchEvent class.
 */
abstract class AbstractUpdateWhereEvent extends AbstractEntityEvent
{
    protected mixed $conditions;

    protected array $data;

    protected array|object $source = [];

    /**
     * @return mixed
     */
    public function getConditions(): mixed
    {
        return $this->conditions;
    }

    /**
     * @param  mixed  $conditions
     *
     * @return  static  Return self to support chaining.
     */
    public function setConditions(mixed $conditions): static
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param  array  $data
     *
     * @return  static  Return self to support chaining.
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array|object
     */
    public function getSource(): object|array
    {
        return $this->source;
    }

    /**
     * @param  array|object  $source
     *
     * @return  static  Return self to support chaining.
     */
    public function setSource(object|array $source): static
    {
        $this->source = $source;

        return $this;
    }
}
