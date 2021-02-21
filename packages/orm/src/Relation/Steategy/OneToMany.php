<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Steategy;

use Windwalker\Data\Collection;
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Relation\RelationCollection;
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\ORM\Strategy\Selector;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The OneToMany class.
 */
class OneToMany extends AbstractRelation
{
    /**
     * @inheritDoc
     */
    public function load(array $data, object $entity): array
    {
        $getter = fn() => $this->createCollection($data);

        RelationProxies::set($entity, $this->getPropName(), $getter);

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function save(array $data, object $entity, ?array $oldData = null): void
    {
        if ($this->onUpdate === Action::NO_ACTION || $this->onUpdate === Action::RESTRICT) {
            return;
        }

        $collection = ReflectAccessor::getValue($entity, $this->getPropName())
            ?? $this->createCollection($data);

        $changed = $this->isChanged($data, $oldData);
        $attachEntities = null;
        $detachEntities = null;
        $keepEntities = null;

        if ($collection->isSync()) {
            $conditions = $this->syncValuesToForeign($oldData, []);

            $entities = $collection->all()
                ->map(fn ($entity) => $this->getORM()->extractEntity($entity));

            if ($this->isFlush()) {
                // If is flush, let's delete all relations and make all attaches
                $this->deleteAllRelatives($conditions);

                $attachEntities = $entities;
            } else {
                // If not flush let's make attach and detach diff
                $oldItems = $this->getORM()
                    ->from($this->getForeignMetadata()->getClassName())
                    ->where($conditions)
                    ->all()
                    ->dump(true);

                [$detachEntities,] = $this->getDetachDiff(
                    $entities,
                    $oldItems,
                    $this->getForeignMetadata()->getKeys(),
                    $data
                );
                [$attachEntities, $keepEntities] = $this->getAttachDiff(
                    $entities,
                    $oldItems,
                    $this->getForeignMetadata()->getKeys(),
                    $data
                );
            }
        } else {
            // Not sync, manually set attach/detach
            $attachEntities = $collection->getAttachedEntities();
            $detachEntities = $collection->getDetachedEntities();
        }

        // Handle Attach
        if ($attachEntities) {
            foreach ($attachEntities as $foreignEntity) {
                $foreignData = $this->getORM()->extractEntity($foreignEntity);
                $foreignData = $this->syncValuesToForeign($data, $foreignData);

                $this->getORM()
                    ->mapper($this->targetTable)
                    ->saveOne($foreignData);
            }
        }

        // Handle Detach
        if ($detachEntities) {
            foreach ($detachEntities as $foreignEntity) {
                $foreignData = $this->getORM()->extractEntity($foreignEntity);

                $foreignData = $this->clearRelativeFields($foreignData);
                $this->getORM()
                    ->mapper($this->targetTable)
                    ->updateOne($foreignData, null, true);
            }
        }

        // Handle changed
        if ($changed) {
            if (!isset($keepEntities)) {
                $conditions = $this->syncValuesToForeign($oldData, []);

                $keepEntities = $this->getORM()
                    ->from($this->getForeignMetadata()->getClassName())
                    ->where($conditions);
            }

            foreach ($keepEntities as $keepEntity) {
                $keepData = $this->getORM()->extractEntity($keepEntity);

                $keepData = $this->handleUpdateRelations($data, $keepData);

                $this->getORM()->updateOne(
                    $this->getForeignMetadata()->getClassName(),
                    $keepData,
                    null,
                    true
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(array $data, object $entity): void
    {
        if ($this->onDelete === Action::NO_ACTION || $this->onDelete === Action::RESTRICT) {
            return;
        }

        if ($this->onDelete === Action::CASCADE) {
            $this->deleteAllRelatives($data);
            return;
        }

        // SET NULL
        $conditions = $this->syncValuesToForeign($data, []);

        $items = $this->getORM()
            ->from($this->getForeignMetadata()->getClassName())
            ->where($conditions);

        foreach ($items as $foreignEntity) {
            $delItem = $this->getORM()->extractEntity($foreignEntity);

            $delItem = $this->clearRelativeFields($delItem);

            $this->getORM()->updateOne(
                $this->getForeignMetadata()->getClassName(),
                $delItem,
                null,
                true
            );
        }
    }

    protected function createCollectionQuery(array $data): Selector
    {
        return $this->getORM()
            ->from($this->targetTable)
            ->where($this->createLoadConditions($data));
    }

    protected function createCollection(array $data): RelationCollection
    {
        return new RelationCollection(
            $this->targetTable,
            $this->createCollectionQuery($data)
        );
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
}
