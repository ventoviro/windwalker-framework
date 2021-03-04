<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Strategy;

use Windwalker\Database\Driver\StatementInterface;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Relation\ForeignTable;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The AbstractRelationStrategy class.
 */
abstract class AbstractRelation implements RelationStrategyInterface, RelationConfigureInterface
{
    use OptionAccessTrait;

    protected ForeignTable $target;

    protected bool $flush;

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
        protected EntityMetadata $metadata,
        protected string $propName,
        ?string $targetTable = null,
        array $fks = [],
        protected string $onUpdate = Action::NO_ACTION,
        protected string $onDelete = Action::NO_ACTION,
        array $options = [],
    ) {
        $this->target = new ForeignTable();

        $this->target($targetTable, $fks);

        $this->prepareOptions([], $options);
        $this->flush((bool) $this->getOption('flush'));
    }

    /**
     * @return EntityMetadata
     */
    public function getMetadata(): EntityMetadata
    {
        return $this->metadata;
    }

    public function getForeignMetadata(): EntityMetadata
    {
        return $this->getORM()->getEntityMetadata($this->getTargetTable());
    }

    public function createLoadConditions(array $data, ?string $alias = null): array
    {
        $conditions = [];

        foreach ($this->getForeignKeys() as $field => $foreign) {
            if ($alias) {
                $foreign = $alias . '.' . $foreign;
            }

            $conditions[$foreign] = $data[$field];
        }

        $conditions = array_merge($this->getMorphs(), $conditions);

        return $conditions;
    }

    /**
     * deleteAllRelatives
     *
     * @param  array  $data
     *
     * @return  StatementInterface[]
     */
    public function deleteAllRelatives(array $data): array
    {
        return $this->getORM()
            ->mapper($this->getTargetTable())
            ->deleteWhere($this->createLoadConditions($data));
    }

    public function clearKeysValues(array $foreignData): array
    {
        $foreignMetadata = $this->getForeignMetadata();

        foreach ($foreignMetadata->getKeys() as $key) {
            $foreignData[$key] = null;
        }

        return $foreignData;
    }

    /**
     * Handle update relation and set matched value to child table.
     *
     * @param  array  $ownerData    The owner entity.
     * @param  array  $foreignData  The relative entity to be handled.
     *
     * @return  array  Return table if you need.
     */
    public function handleUpdateRelations(array $ownerData, array $foreignData): array
    {
        if ($this->onUpdate === Action::CASCADE) {
            // Handle Cascade
            return $this->syncValuesToForeign($ownerData, $foreignData);
        }

        // Handle Set NULL
        if ($this->onUpdate === Action::SET_NULL && $this->isForeignDataDifferent($ownerData, $foreignData)) {
            return $this->clearRelativeFields($foreignData);
        }

        return $foreignData;
    }

    /**
     * Sync parent fields value to child table.
     *
     * @param  array  $ownerData
     * @param  array  $foreignData  The child table to be handled.
     *
     * @return  array  Return rel data if you need.
     */
    protected function syncValuesToForeign(array $ownerData, array $foreignData): array
    {
        foreach ($this->getForeignKeys() as $field => $foreign) {
            $foreignData[$foreign] = $ownerData[$field];
        }

        return $foreignData;
    }

    protected function getRelativeValues(array $data, bool $foreign = false): array
    {
        $keys = $foreign
            ? array_values($this->getForeignKeys())
            : array_keys($this->getForeignKeys());

        return Arr::only($data, $keys);
    }

    /**
     * Clear value to all relative children fields.
     *
     * @param  array  $foreignData  The child table to be handled.
     *
     * @return  array  Return data if you need.
     */
    protected function clearRelativeFields(array $foreignData): array
    {
        foreach ($this->getForeignKeys() as $field => $foreign) {
            $foreignData[$foreign] = null;
        }

        return $foreignData;
    }

    /**
     * Is fields changed. If any field changed, means we have to do something to children.
     *
     * @param  array  $ownerData
     * @param  array  $foreignData  The child data to be handled.
     *
     * @return  bool  Something changed of not.
     */
    public function isForeignDataDifferent(array $ownerData, array $foreignData): bool
    {
        // If any key changed, set all fields as NULL.
        foreach ($this->getForeignKeys() as $field => $foreign) {
            if ($foreignData[$foreign] != $ownerData[$field]) {
                return true;
            }
        }

        return false;
    }

    protected function isChanged(array $data, ?array $oldData): bool
    {
        $fks = $this->getForeignKeys();

        return $oldData ? !Arr::arrayEquals(
            Arr::only($data, array_keys($fks)),
            Arr::only($oldData, array_keys($fks)),
        ) : false;
    }

    public function target(?string $table, array|string $ownerKey, ?string $foreignKey = null): static
    {
        $fks = $ownerKey;

        if (is_string($fks)) {
            TypeAssert::assert(
                $foreignKey !== null,
                '{caller} argument #2 and #3, should have a foreign key pair, the foreign key is {value}.',
                $foreignKey
            );

            $fks = [$fks => $foreignKey];
        } else {
            $fks = $ownerKey;
        }

        $this->target->setName($table);

        $this->foreignKeys($fks);

        return $this;
    }

    public function foreignKeys(array $fks): static
    {
        $this->target->setFks($fks);

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
        return $this->target->getName();
    }

    /**
     * @return ForeignTable
     */
    public function getTarget(): ForeignTable
    {
        return $this->target;
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
        return $this->target->getFks();
    }

    public function getOwnerKeys(): array
    {
        return array_keys($this->getForeignKeys());
    }

    public function morphBy(...$columns): static
    {
        $morphs = Arr::collapse($columns, true);

        $this->target->setMorphs($morphs);

        return $this;
    }

    /**
     * @return array
     */
    public function getMorphs(): array
    {
        return $this->target->getMorphs();
    }

    protected function mergeMorphValues(array $data): array
    {
        return array_merge($data, $this->getMorphs());
    }

    protected function isMorphChanged(array $data): bool
    {
        foreach ($this->getMorphs() as $field => $value) {
            if (($data[$field] ?? null) !== $value) {
                return false;
            }
        }

        return true;
    }
}
