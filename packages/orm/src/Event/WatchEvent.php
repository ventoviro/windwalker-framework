<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The WatchEvent class.
 */
class WatchEvent extends AbstractSaveEvent
{
    protected AbstractEntityEvent|null $originEvent = null;

    protected bool $isUpdateWhere = false;

    protected mixed $value;

    protected mixed $oldValue;

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param  mixed  $value
     *
     * @return  static  Return self to support chaining.
     */
    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOldValue(): mixed
    {
        return $this->oldValue;
    }

    /**
     * @param  mixed  $oldValue
     *
     * @return  static  Return self to support chaining.
     */
    public function setOldValue(mixed $oldValue): static
    {
        $this->oldValue = $oldValue;

        return $this;
    }

    /**
     * @return AbstractEntityEvent|null
     */
    public function getOriginEvent(): ?AbstractEntityEvent
    {
        return $this->originEvent;
    }

    /**
     * @param  AbstractEntityEvent|null  $originEvent
     *
     * @return  static  Return self to support chaining.
     */
    public function setOriginEvent(?AbstractEntityEvent $originEvent): static
    {
        $this->originEvent = $originEvent;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUpdateWhere(): bool
    {
        return $this->isUpdateWhere;
    }

    /**
     * @param  bool  $isUpdateWhere
     *
     * @return  static  Return self to support chaining.
     */
    public function setIsUpdateWhere(bool $isUpdateWhere): static
    {
        $this->isUpdateWhere = $isUpdateWhere;

        return $this;
    }
}
