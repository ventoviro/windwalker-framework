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
 * The AbstractSaveEvent class.
 */
abstract class AbstractSaveEvent extends AbstractEntityEvent
{
    public const TYPE_CREATE = 'create';
    public const TYPE_UPDATE = 'update';

    protected string $type;

    protected array $data;

    protected ?array $oldData = null;

    protected int $options = 0;

    protected array|object $source = [];

    /**
     * @return array
     */
    public function &getData(): array
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param  string  $type
     *
     * @return  static  Return self to support chaining.
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getOldData(): ?array
    {
        return $this->oldData;
    }

    /**
     * @param  array|null  $oldData
     *
     * @return  static  Return self to support chaining.
     */
    public function setOldData(?array $oldData): static
    {
        $this->oldData = $oldData;

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

    public function isCreate(): bool
    {
        return $this->getType() === static::TYPE_CREATE;
    }

    public function isUpdate(): bool
    {
        return $this->getType() === static::TYPE_UPDATE;
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
