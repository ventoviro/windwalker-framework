<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Event;

/**
 * The AbstractDeleteEvent class.
 */
abstract class AbstractDeleteEvent extends AbstractEntityEvent
{
    protected mixed $conditions;

    protected ?array $data = null;

    protected int $options = 0;

    protected ?object $entity = null;

    /**
     * @return mixed
     */
    public function &getConditions(): mixed
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
     * @return array|null
     */
    public function &getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param  array|null  $data
     *
     * @return  static  Return self to support chaining.
     */
    public function setData(?array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return object|null
     */
    public function getEntity(): ?object
    {
        return $this->entity;
    }

    /**
     * @param  object|null  $entity
     *
     * @return  static  Return self to support chaining.
     */
    public function setEntity(?object $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return int
     */
    public function &getOptions(): int
    {
        return $this->options;
    }

    /**
     * @param  int  $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(int $options): static
    {
        $this->options = $options;

        return $this;
    }
}
