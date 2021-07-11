<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Strategy;

use LogicException;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Relation\ForeignTable;
use Windwalker\ORM\Relation\RelationCollection;
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\ORM\SelectorQuery;
use Windwalker\Query\Clause\JoinClause;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Reflection\ReflectAccessor;

use function Windwalker\Query\val;

/**
 * The ManyToMany class.
 */
class ManyToMany extends AbstractRelation
{
    use HasManyTrait;

    protected ForeignTable $map;

    /**
     * @inheritDoc
     */
    public function __construct(
        EntityMetadata $metadata,
        string $propName,
        ?string $mapTable = null,
        array $mapFks = [],
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

        $this->map = new ForeignTable();

        $this->mapBy($mapTable, ...$mapFks);
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
            $this->getTargetTable(),
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

        [$attachEntities, $detachEntities, $keepEntities] = $this->diffRelated($data, $entity, $oldData);

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
            Arr::only($data, array_keys($this->getMapForeignKeys())),
            Arr::only($oldData, array_keys($this->getMapForeignKeys())),
        ) : false;
    }

    /**
     * @inheritDoc
     */
    public function delete(array $data, object $entity): void
    {
        if ($this->onDelete === Action::NO_ACTION || $this->onDelete === Action::RESTRICT) {
            return;
        }

        $mapMetadata = $this->getMapMetadata();

        foreach ($this->createCollection($data) as $foreignEntity) {
            // CASCADE
            if ($this->onDelete === Action::CASCADE) {
                $this->getORM()
                    ->mapper($this->getTargetTable())
                    ->deleteWhere($foreignEntity);
            }

            // SET NULL
            $foreignData = $this->getORM()->extractEntity($foreignEntity);

            $mapData = $this->syncMapData([], $data, $foreignData);

            $mapMetadata->getEntityMapper()->deleteWhere($mapData);
        }
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
        $mapMetadata = $this->getMapMetadata();

        $results = [];

        foreach ($this->createCollectionQuery($data) as $foreignEntity) {
            $results[] = $this->getORM()->mapper($this->getTargetTable())->deleteWhere($foreignEntity);

            $foreignData = $this->getORM()->extractEntity($foreignEntity);

            $mapData = $this->syncMapData([], $data, $foreignData);

            $mapMetadata->getEntityMapper()->deleteWhere($mapData);
        }

        return $results;
    }

    protected function createCollectionQuery(array $data): SelectorQuery
    {
        $foreignMetadata = $this->getForeignMetadata();
        $alias = $foreignMetadata->getTableAlias();

        $mapMetadata = $this->getMapMetadata();
        $mapAlias = $mapMetadata->getTableAlias();

        return $this->getORM()
            ->from($foreignMetadata->getClassName(), $alias)
            ->leftJoin(
                $mapMetadata->getClassName(),
                $mapAlias,
                function (JoinClause $joinClause) use ($alias, $mapAlias) {
                    foreach ($this->getForeignKeys() as $mk => $fk) {
                        $joinClause->on("$mapAlias.$mk", '=', "$alias.$fk");
                    }

                    foreach ($this->getMap()->getMorphs() as $field => $value) {
                        $joinClause->on("$mapAlias.$field", '=', val($value));
                    }
                }
            )
            ->where($this->createLoadConditions($data, $mapAlias))
            ->groupByJoins();
    }

    public function createLoadConditions(array $data, ?string $alias = null): array
    {
        $conditions = [];

        foreach ($this->getMapForeignKeys() as $field => $mapFk) {
            if ($alias) {
                $mapFk = $alias . '.' . $mapFk;
            }

            $conditions[$mapFk] = $data[$field];
        }

        $mapAlias = $this->getMapMetadata()->getTableAlias();

        foreach ($this->getMap()->getMorphs() as $field => $value) {
            if ($mapAlias) {
                $field = $mapAlias . '.' . $field;
            }

            $conditions[$field] = $value;
        }

        $foreignAlias = $this->getForeignMetadata()->getTableAlias();

        foreach ($this->getMorphs() as $field => $value) {
            if ($foreignAlias) {
                $field = $foreignAlias . '.' . $field;
            }

            $conditions[$field] = $value;
        }

        return $conditions;
    }

    public function getMapMetadata(): EntityMetadata
    {
        return $this->getORM()->getEntityMetadata($this->getMapTable());
    }

    /**
     * @return array
     */
    public function getMapForeignKeys(): array
    {
        return $this->map->getFks();
    }

    /**
     * @return string|null
     */
    public function getMapTable(): ?string
    {
        return $this->map->getName();
    }

    /**
     * @param  string|null  $mapTable
     * @param  mixed        ...$columns
     *
     * @return  static  Return self to support chaining.
     */
    public function mapBy(?string $mapTable, ...$columns): static
    {
        $this->map->setName($mapTable);

        $this->setMapForeignKeys(...$columns);

        return $this;
    }

    public function mapMorphBy(...$columns): static
    {
        $this->map->setMorphs($this->handleColumnMapping($columns));

        return $this;
    }

    /**
     * @param  array  $columns
     *
     * @return  static  Return self to support chaining.
     */
    public function setMapForeignKeys(...$columns): static
    {
        $this->map->setFks($this->handleColumnMapping($columns));

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
        foreach ($this->getMapForeignKeys() as $field => $foreign) {
            $mapData[$foreign] = $ownerData[$field];
        }

        // Prepare map table and target table mapping
        foreach ($this->getForeignKeys() as $field => $foreign) {
            $mapData[$field] = $foreignData[$foreign];
        }

        return $mapData;
    }

    /**
     * @return ForeignTable
     */
    public function getMap(): ForeignTable
    {
        return $this->map;
    }

    protected function getDetachDiff(iterable $items, array $oldItems, array $compareKeys, array $ownerData): array
    {
        $keep = [];
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
        $keep = [];
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
            throw new LogicException(
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
            if ($foreignMetadata->getEntityMapper()->isNew($foreignData)) {
                $foreignData = $this->mergeMorphValues($foreignData);

                $foreignEntity = $foreignMetadata->getEntityMapper()
                    ->createOne($foreignData);

                $foreignData = $this->getORM()->extractEntity($foreignEntity);
            }

            // After get foreign data AI id, now can create map
            $mapData = $this->syncMapData($mapData, $data, $foreignData);
            $mapData = $this->mergeMapMorphValues($mapData);

            $mapEntity = $this->getORM()
                ->hydrateEntity(
                    $mapData,
                    $mapMetadata->getEntityMapper()->toEntity($mapEntity ?? [])
                );

            $mapMetadata->getEntityMapper()->createOne($mapEntity);
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

            $mapMetadata->getEntityMapper()->deleteWhere($mapData);
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
            $mapEntity ??= $mapMetadata->getEntityMapper()->toEntity([]);

            // Sync old values to map data
            $oldMapData = $this->syncMapData($oldMapData, $oldData, $foreignData);
            $oldMapConditions = $this->syncMapData([], $oldData, $foreignData);

            if ($this->onUpdate === Action::CASCADE) {
                // Try get DB map if exists
                $mapData = $mapMetadata->getEntityMapper()
                    ->select()
                    ->where($oldMapConditions)
                    ->get()
                    ?->dump();

                $mapData ??= [];

                // $this->handleUpdateRelations($data, $oldMapConditions);

                foreach ($this->getMapForeignKeys() as $field => $mapFk) {
                    $mapData[$mapFk] = $data[$field];
                }

                if ($mapMetadata->getMainKey()) {
                    $mapMetadata->getEntityMapper()
                        ->updateOne(
                            $mapData,
                            null,
                            EntityMapper::UPDATE_NULLS
                        );
                } else {
                    $mapMetadata->getEntityMapper()
                        ->updateWhere(
                            $mapData,
                            $oldMapConditions
                        );
                }

                $this->getORM()->hydrateEntity($mapData, $mapEntity);
            }

            // Handle Set NULL
            if ($this->onUpdate === Action::SET_NULL && $this->isMapDataDifferent($data, $oldMapData)) {
                $mapMetadata->getEntityMapper()->deleteWhere($oldMapConditions);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function isMapDataDifferent(array $ownerData, array $mapData): bool
    {
        // If any key changed, set all fields as NULL.
        foreach ($this->getMapForeignKeys() as $field => $mapFk) {
            if ($mapData[$mapFk] != $ownerData[$field]) {
                return true;
            }
        }

        return false;
    }

    protected function mergeMorphValues(array $data): array
    {
        return array_merge($data, $this->getMorphs());
    }

    protected function mergeMapMorphValues(array $data): array
    {
        return array_merge($data, $this->getMap()->getMorphs());
    }
}
