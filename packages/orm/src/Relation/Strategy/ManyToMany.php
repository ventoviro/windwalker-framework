<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Strategy;

use Windwalker\Data\Collection;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Relation\RelationCollection;
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\ORM\Strategy\Selector;
use Windwalker\Query\Clause\JoinClause;
use Windwalker\Query\Query;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The ManyToMany class.
 */
class ManyToMany extends AbstractRelation
{
    use HasManyTrait;

    /**
     * @inheritDoc
     */
    public function __construct(
        EntityMetadata $metadata,
        string $propName,
        protected ?string $mapTable = null,
        protected array $mapFks = [],
        ?string $targetTable = null,
        array $fks = [],
        string $onUpdate = Action::NO_ACTION,
        string $onDelete = Action::NO_ACTION,
        array $options = []
    ) {
        parent::__construct(
            $metadata,
            $propName,
            $targetTable,
            $fks,
            $onUpdate,
            $onDelete,
            $options
        );
    }

    /**
     * @inheritDoc
     */
    public function load(array $data, object $entity): array
    {
        $getter = fn() => $this->createCollection($data);

        RelationProxies::set($entity, $this->getPropName(), $getter);

        return $data;
    }

    protected function createCollection(array $data): RelationCollection
    {
        return new RelationCollection(
            $this->targetTable,
            $this->createCollectionQuery($data)
        );
    }

    /**
     * @inheritDoc
     */
    public function save(array $data, object $entity, ?array $oldData = null): void
    {
        if ($this->onUpdate === Action::NO_ACTION || $this->onUpdate === Action::RESTRICT) {
            return;
        }

        $metadata        = $this->getMetadata();
        $foreignMetadata = $this->getForeignMetadata();
        $mapMetadata     = $this->getMapMetadata();

        [$attachEntities, $detachEntities, $keepEntities] = $this->diffRelated($data, $entity, $oldData);

        // $mapProp = $foreignMetadata->getColumn($mapMetadata->getTableAlias())?->getProperty();
        //
        // $updateCondFields = [
        //     ...$metadata->getKeys(),
        //     ...$foreignMetadata->getKeys()
        // ];

        // Handle Attach
        if ($attachEntities) {
            $this->attachEntities($attachEntities, $data);
        }

        // Handle Detach
        if ($detachEntities) {
            $this->detachEntities($detachEntities, $oldData);
        }

        // Handle changed
        if ($this->isChanged($data, $oldData)) {
            if ($keepEntities === null) {
                $keepEntities = $this->createCollectionQuery($oldData);
            }

            $this->changeEntities($keepEntities, $data, $oldData);
        }
    }

    protected function isChanged(array $data, ?array $oldData): bool
    {
        return $oldData ? !Arr::arrayEquals(
            Arr::only($data, array_keys($this->mapFks)),
            Arr::only($oldData, array_keys($this->mapFks)),
        ) : false;
    }

    /**
     * @inheritDoc
     */
    public function delete(array $data, object $entity): void
    {
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
            ->mapper($this->targetTable)
            ->delete($this->createLoadConditions($data));
    }

    protected function createCollectionQuery(array $data): Selector
    {
        $foreignMetadata = $this->getForeignMetadata();
        $foreignTable    = $foreignMetadata->getTableName();
        $foreignAlias    = $foreignMetadata->getTableAlias();

        $mapMetadata = $this->getMapMetadata();
        $mapTable    = $mapMetadata->getTableName();
        $mapAlias    = $mapMetadata->getTableAlias();

        return $this->getORM()
            ->from($foreignTable, $foreignAlias)
            ->leftJoin(
                $mapTable,
                $mapMetadata->getTableAlias(),
                function (JoinClause $joinClause) use ($foreignAlias, $mapAlias) {
                    foreach ($this->getForeignKeys() as $mapKey => $foreignKey) {
                        $joinClause->on(
                            "$foreignAlias.$foreignKey",
                            "$mapAlias.$mapKey"
                        );
                    }
                }
            )
            ->where($this->createLoadConditions($data, $mapAlias))
            ->groupByJoins();
    }

    public function createLoadConditions(array $data, ?string $alias = null): array
    {
        $conditions = [];

        foreach ($this->mapFks as $field => $mapFk) {
            if ($alias) {
                $mapFk = $alias . '.' . $mapFk;
            }

            $conditions[$mapFk] = $data[$field];
        }

        return $conditions;
    }

    public function getMapMetadata(): EntityMetadata
    {
        return $this->getORM()->getEntityMetadata($this->mapTable);
    }

    /**
     * @return array
     */
    public function getMapForeignKeys(): array
    {
        return $this->mapFks;
    }

    /**
     * @return string|null
     */
    public function getMapTable(): ?string
    {
        return $this->mapTable;
    }

    /**
     * @param  string|null   $mapTable
     * @param  array|string  $ownerKey
     * @param  string|null   $mapKey
     *
     * @return  static  Return self to support chaining.
     */
    public function mapBy(?string $mapTable, array|string $ownerKey, ?string $mapKey = null): static
    {
        $fks = $ownerKey;

        if (is_string($fks)) {
            TypeAssert::assert(
                $mapKey !== null,
                '{caller} argument #2 and #3, should have a foreign key pair, the foreign key is {value}.',
                $mapKey
            );

            $fks = [$fks => $mapKey];
        } else {
            $fks = $ownerKey;
        }

        $this->mapTable = $mapTable;

        $this->setMapForeignKeys($fks);

        return $this;
    }

    /**
     * @param  array  $mapFks
     *
     * @return  static  Return self to support chaining.
     */
    public function setMapForeignKeys(array $mapFks): static
    {
        $this->mapFks = $mapFks;

        return $this;
    }

    /**
     * syncMapData
     *
     * @param  array  $mapData
     * @param  array  $ownerData
     * @param  array  $foreignData
     *
     * @return  array|object
     */
    protected function syncMapData(array $mapData, array $ownerData, array $foreignData): array|object
    {
        // Prepare parent table and map table mapping
        foreach ($this->mapFks as $field => $foreign) {
            $mapData[$foreign] = $ownerData[$field];
        }

        // Prepare map table and target table mapping
        foreach ($this->fks as $field => $foreign) {
            $mapData[$field] = $foreignData[$foreign];
        }

        return $mapData;
    }

    protected function getDetachDiff(iterable $items, array $oldItems, array $compareKeys, array $ownerData): array
    {
        $keep    = [];
        $detaches = [];

        foreach ($oldItems as $old) {
            $oldValues = Arr::only($old, $compareKeys);

            foreach ($items as $item) {
                // Check this old item has at-least 1 new item matched.
                if (Arr::arrayEquals($oldValues, Arr::only($item, $compareKeys))) {
                    $keep[] = $old;
                    continue 2;
                }
            }

            // If no matched, mark this old item to be delete.
            $detaches[] = $old;
        }

        return [$detaches, $keep];
    }

    protected function getAttachDiff(iterable $items, array $oldItems, array $compareKeys, array $ownerData): array
    {
        $keep    = [];
        $creates = [];

        foreach ($items as $item) {
            $values = Arr::only($item, $compareKeys);

            foreach ($oldItems as $old) {
                // Check this new item has at-least 1 old item matched.
                if (Arr::arrayEquals(Arr::only($old, $compareKeys), $values)) {
                    $keep[] = $item;
                    continue 2;
                }
            }

            // If no matched, mark this new item to be create.
            $creates[] = $item;
        }

        return [$creates, $keep];
    }

    public function attachEntities(iterable $entities, array $data): void
    {
        $mapMetadata = $this->getMapMetadata();
        $foreignMetadata = $this->getForeignMetadata();
        $mapAlias = $mapMetadata->getTableAlias();
        $prop = $foreignMetadata->getColumn($mapAlias)?->getProperty()?->getName();

        if ($prop === null) {
            throw new \LogicException(
                sprintf(
                    "Please add '%s' column with type %s to entity %s",
                    $mapAlias,
                    RelationCollection::class,
                    $foreignMetadata->getClassName()
                )
            );
        }

        foreach ($entities as $foreignEntity) {
            $foreignData = $this->getORM()->extractEntity($foreignEntity);

            // Attempt to get map data if exists
            if (is_object($foreignEntity)) {
                $mapEntity = ReflectAccessor::getValue($foreignEntity, $prop);
            } else {
                $mapEntity = $foreignEntity[$mapAlias] ?? null;
            }

            // Otherwise create new one
            $mapData = $mapEntity ? $this->getORM()->extractEntity($mapEntity) : [];

            // Create Foreign data
            if ($foreignMetadata->getMapper()->isNew($foreignData)) {
                $foreignEntity = $foreignMetadata->getMapper()
                    ->createOne($foreignData);

                $foreignData = $this->getORM()->extractEntity($foreignEntity);
            }

            // After get foreign data AI id, now can create map
            $mapData = $this->syncMapData($mapData, $data, $foreignData);

            $mapEntity = $this->getORM()
                ->hydrateEntity(
                    $mapData,
                    $mapMetadata->getMapper()->toEntity($mapEntity ?? [])
                );

            $mapMetadata->getMapper()->createOne($mapEntity);
        }
    }

    public function detachEntities(iterable $entities, ?array $oldData): void
    {
        if ($oldData === null) {
            return;
        }

        $mapMetadata = $this->getMapMetadata();

        foreach ($entities as $foreignEntity) {
            $foreignData = $this->getORM()->extractEntity($foreignEntity);

            $mapData = $this->syncMapData([], $oldData, $foreignData);

            $mapMetadata->getMapper()->delete($mapData);
        }
    }

    public function changeEntities(iterable $entities, array $data, ?array $oldData): void
    {
        if ($oldData === null) {
            return;
        }

        $mapMetadata = $this->getMapMetadata();
        $mapAlias = $mapMetadata->getTableAlias();

        foreach ($entities as $foreignEntity) {
            $foreignData = $this->getORM()->extractEntity($foreignEntity);

            // Attempt to get map data if exists
            $mapEntity = $foreignData[$mapAlias] ?? null;

            // Otherwise create new one
            $oldMapData = $mapEntity ? $this->getORM()->extractEntity($mapEntity) : [];
            $mapEntity ??= $mapMetadata->getMapper()->toEntity([]);

            // Sync old values to map data
            $oldMapData = $this->syncMapData($oldMapData, $oldData, $foreignData);
            $oldMapConditions = $this->syncMapData([], $oldData, $foreignData);

            if ($this->onUpdate === Action::CASCADE) {
                // Try get DB map if exists
                $mapData = $mapMetadata->getMapper()
                    ->select()
                    ->where($oldMapConditions)
                    ->get()
                    ?->dump();

                $mapData ??= [];

                // $this->handleUpdateRelations($data, $oldMapConditions);

                foreach ($this->mapFks as $field => $mapFk) {
                    $mapData[$mapFk] = $data[$field];
                }

                if ($mapMetadata->getMainKey()) {
                    $mapMetadata->getMapper()
                        ->updateOne(
                            $mapData,
                            null,
                            true
                        );
                } else {
                    $mapMetadata->getMapper()
                        ->updateBatch(
                            $mapData,
                            $oldMapConditions
                        );
                }

                $this->getORM()->hydrateEntity($mapData, $mapEntity);
            }

            // Handle Set NULL
            if ($this->onUpdate === Action::SET_NULL && $this->isMapDataDifferent($data, $oldMapData)) {
                $mapMetadata->getMapper()->delete($oldMapConditions);
            }
        }
    }

    /**
     * @inheritDoc
     */
    // public function handleUpdateRelations(array $ownerData, array $mapData): array
    // {
    //     if ($this->onUpdate === Action::CASCADE) {
    //         // Handle Cascade
    //         return $this->syncValuesToForeign($ownerData, $mapData);
    //     }
    //
    //     // Handle Set NULL
    //     if ($this->onUpdate === Action::SET_NULL && $this->isForeignDataDifferent($ownerData, $foreignData)) {
    //         return $this->clearRelativeFields($mapData);
    //     }
    //
    //     return $mapData;
    // }

    /**
     * @inheritDoc
     */
    public function isMapDataDifferent(array $ownerData, array $mapData): bool
    {
        // If any key changed, set all fields as NULL.
        foreach ($this->mapFks as $field => $mapFk) {
            if ($mapData[$mapFk] != $ownerData[$field]) {
                return true;
            }
        }

        return false;
    }
}