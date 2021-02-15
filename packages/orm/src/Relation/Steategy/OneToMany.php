<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Steategy;

use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Relation\RelationCollection;
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\ORM\Strategy\Selector;
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
        $getter = fn() => $this->createCollection($entity::class, $data);

        RelationProxies::set($entity, $this->getPropName(), $getter);

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function save(array $data, object $entity): void
    {
        if ($this->onUpdate === Action::NO_ACTION || $this->onUpdate === Action::RESTRICT) {
            return;
        }

        $collection = ReflectAccessor::getValue($entity, $this->getPropName())
            ?? $this->createCollection($entity::class, $data);

        if ($this->isFlush()) {
            $this->deleteAllRelatives($data);
        }

        if ($collection->isSync()) {
            // If Collection set flush all, diff the all added entities
            $entities = $collection->all();
            $conditions = $this->syncValuesToRelData($data, []);

            $this->getORM()
                ->mapper($this->targetTable)
                ->sync(
                    $entities,
                    $conditions,
                    array_values($this->getForeignKeys())
                );
        } else {
            // If not flush all, separate add and remove

            // Handle Add
            if ($entities = $collection->getAddedEntities()) {
                foreach ($entities as $relEntity) {
                    $relData = $this->getORM()->extractEntity($relEntity);
                    $relData = $this->syncValuesToRelData($data, $relData);

                    $this->getORM()
                        ->mapper($this->targetTable)
                        ->saveOne($relData, null, true);
                }
            }

            // Handle Remove
            if ($entities = $collection->getRemoveEntities()) {
                foreach ($entities as $relEntity) {
                    $relData = $this->getORM()->extractEntity($relEntity);
                    $relData = $this->clearRelativeFields($relData);

                    $this->getORM()
                        ->mapper($this->targetTable)
                        ->updateOne($relData);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(array $data, object $entity): void
    {
    }

    protected function createCollectionQuery(array $data): Selector
    {
        return $this->getORM()
            ->from($this->targetTable)
            ->where($this->createLoadConditions($data));
    }

    protected function createCollection(string $class, array $data): RelationCollection
    {
        return new RelationCollection(
            $class,
            $this->createCollectionQuery($data)
        );
    }
}
