<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Steategy;

use Windwalker\Database\Driver\StatementInterface;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Relation\Action;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The AbstractRelationStrategy class.
 */
abstract class AbstractRelation implements RelationStrategyInterface, RelationConfigureInterface
{
    use OptionAccessTrait;

    protected string $propName;

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
     * @param  string          $propName
     * @param  string|null     $targetTable
     * @param  array           $fks
     * @param  string          $onUpdate
     * @param  string          $onDelete
     * @param  array           $options
     */
    public function __construct(
        EntityMetadata $metadata,
        string $propName,
        ?string $targetTable = null,
        array $fks = [],
        string $onUpdate = Action::NO_ACTION,
        string $onDelete = Action::NO_ACTION,
        array $options = [],
    ) {
        $this->target($targetTable, $fks);

        $this->metadata = $metadata;
        $this->propName = $propName;
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

    public function getTargetMetadata(): EntityMetadata
    {
        return $this->getORM()->getEntityMetadata($this->targetTable);
    }

    public function createLoadConditions(array $data): array
    {
        $conditions = [];

        foreach ($this->fks as $field => $foreign) {
            $conditions[$foreign] = $data[$field];
        }

        return $conditions;
    }

    /**
     * deleteAllRelatives
     *
     * @param  array  $relData
     *
     * @return  StatementInterface[]
     */
    public function deleteAllRelatives(array $relData): array
    {
        return $this->getORM()
            ->mapper($this->targetTable)
            ->delete($this->createLoadConditions($relData));
    }

    public function clearKeysValues(array $relData): array
    {
        $relMetadata = $this->getORM()->getEntityMetadata($this->targetTable);

        foreach ($relMetadata->getKeys() as $key)
        {
            $relData[$key] = null;
        }

        return $relData;
    }

    /**
     * Handle update relation and set matched value to child table.
     *
     * @param  array  $selfData  The self entity.
     * @param  array  $relData   The relative entity to be handled.
     *
     * @return  array  Return table if you need.
     */
    public function handleUpdateRelations(array $selfData, array $relData): array
    {
        if ($this->onUpdate === Action::CASCADE) {
            // Handle Cascade
            return $this->syncValuesToRelData($selfData, $relData);
        }

        // Handle Set NULL
        if ($this->onUpdate === Action::SET_NULL && $this->isChanged($selfData, $relData)) {
            return $this->clearRelativeFields($relData);
        }

        return $relData;
    }

    /**
     * Handle delete relation, if is CASCADE, mark child table to delete. If is SET NULL, set all children fields to
     * NULL.
     *
     * @param  array  $selfData  The self entity.
     * @param  array  $relData   The relative entity to be handled.
     *
     * @return array Return table if you need.
     */
    public function handleDeleteRelations(array $selfData, array $relData): array
    {
        return $this->clearRelativeFields($relData);
    }

    /**
     * Sync parent fields value to child table.
     *
     * @param  array  $selfData
     * @param  array  $relData  The child table to be handled.
     *
     * @return  array  Return rel data if you need.
     */
    protected function syncValuesToRelData(array $selfData, array $relData): array
    {
        foreach ($this->fks as $field => $foreign) {
            $relData[$foreign] = $selfData[$field];
        }

        return $relData;
    }

    /**
     * Clear value to all relative children fields.
     *
     * @param  array  $relData  The child table to be handled.
     *
     * @return  array  Return data if you need.
     */
    protected function clearRelativeFields(array $relData): array
    {
        foreach ($this->fks as $field => $foreign) {
            $relData[$foreign] = null;
        }

        return $relData;
    }

    /**
     * Is fields changed. If any field changed, means we have to do something to children.
     *
     * @param  array  $selfData
     * @param  array  $relData  The child data to be handled.
     *
     * @return  bool  Something changed of not.
     */
    public function isChanged(array $selfData, array $relData): bool
    {
        // If any key changed, set all fields as NULL.
        foreach ($this->fks as $field => $foreign) {
            if ($relData[$foreign] != $selfData[$field]) {
                return true;
            }
        }

        return false;
    }

    public function target(?string $table, array $fks): static
    {
        $this->targetTable = $table;
        $this->foreignKeys($fks);

        return $this;
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
    public function getPropName(): string
    {
        return $this->propName;
    }

    // Todo: Try use column name to get value
    public function getColumnName(): string
    {
        //
    }

    /**
     * @param  string  $propName
     *
     * @return  static  Return self to support chaining.
     */
    public function propName(string $propName): static
    {
        $this->propName = $propName;

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
