<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Steategy;

use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Strategy\Selector;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The AbstractRelationStrategy class.
 */
abstract class AbstractRelationStrategy implements RelationStrategyInterface
{
    use OptionAccessTrait;

    protected string $field;

    protected ?string $targetTable;

    protected array $fks;

    protected string $onUpdate;

    protected string $onDelete;

    protected bool $flush;

    /**
     * @var EntityMetadata
     */
    protected EntityMetadata $metadata;

    /**
     * AbstractRelationStrategy constructor.
     *
     * @param  EntityMetadata  $metadata
     * @param  string          $field
     * @param  string          $targetTable
     * @param  array           $fks
     * @param  string          $onUpdate
     * @param  string          $onDelete
     * @param  array           $options
     */
    public function __construct(
        EntityMetadata $metadata,
        string $field,
        ?string $targetTable = null,
        array $fks = [],
        string $onUpdate = Action::NO_ACTION,
        string $onDelete = Action::NO_ACTION,
        array $options = [],
    ) {
        $this->target($targetTable, $fks);

        $this->metadata = $metadata;
        $this->field    = $field;
        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
        $this->options  = $options;
        $this->flush((bool) $this->getOption('flush'));
    }

    /**
     * @return EntityMetadata
     */
    public function getMetadata(): EntityMetadata
    {
        return $this->metadata;
    }

    public function createLoadQuery(array $data): Selector
    {
        $metadata = $this->getMetadata();
        $conditions = [];

        foreach ($this->fks as $field => $foreign) {
            $conditions[$foreign] = $data[$field];
        }

        $query = $this->getORM()->from($metadata->getClassName());
        $query->where($conditions);

        return $query;
    }

    public function target(string $table, array|string $fks)
    {
        $this->targetTable = $table;
    }

    public function foreignKeys(array $fks): static
    {
        $this->fks = $fks;

        return $this;
    }

    /**
     * @return ORM
     */
    public function getORM(): ORM
    {
        return $this->getMetadata()->getORM();
    }

    /**
     * @return bool
     */
    public function isFlush(): bool
    {
        return $this->flush;
    }

    /**
     * @param  bool  $flush
     *
     * @return  static  Return self to support chaining.
     */
    public function flush(bool $flush): static
    {
        $this->flush = $flush;

        return $this;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param  string  $field
     *
     * @return  static  Return self to support chaining.
     */
    public function field(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getTargetTable(): string
    {
        return $this->targetTable;
    }

    /**
     * @return string
     */
    public function getOnUpdate(): string
    {
        return $this->onUpdate;
    }

    /**
     * @param  string  $onUpdate
     *
     * @return  static  Return self to support chaining.
     */
    public function onUpdate(string $onUpdate): static
    {
        $this->onUpdate = $onUpdate;

        return $this;
    }

    /**
     * @return string
     */
    public function getOnDelete(): string
    {
        return $this->onDelete;
    }

    /**
     * @param  string  $onDelete
     *
     * @return  static  Return self to support chaining.
     */
    public function onDelete(string $onDelete): static
    {
        $this->onDelete = $onDelete;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param  array  $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getForeignKeys(): array
    {
        return $this->fks;
    }
}
