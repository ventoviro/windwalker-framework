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
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The OneToOne class.
 */
class OneToOne extends AbstractRelationStrategy
{
    /**
     * @inheritDoc
     */
    public function load(array $data, object $entity): array
    {
        $getter = fn() => $this->getORM()
            ->findOne(
                $this->targetTable,
                $this->createLoadConditions($data)
            );

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

        $relEntity = ReflectAccessor::getValue($entity, $this->getPropName());

        if ($relEntity === null && $this->onUpdate === Action::CASCADE) {
            $relEntity = RelationProxies::call($entity, $this->getPropName());
        }

        if ($relEntity === null) {
            return;
        }

        $relData = $this->getORM()->extractEntity($relEntity);
        $relData = $this->handleUpdateRelations($data, $relData);

        if ($this->isFlush()) {
            $this->deleteAllRelatives($relData);
            $relData = $this->clearKeysValues($relData);
        }

        $this->getORM()
            ->mapper($this->targetTable)
            ->saveOne(
                $relData,
                null,
                true
            );
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
        $relEntity = ReflectAccessor::getValue($entity, $this->getPropName())
            ?? RelationProxies::call($entity, $this->getPropName());

        if ($relEntity === null) {
            return;
        }

        $relData = $this->getORM()->extractEntity($relEntity);
        $relData = $this->handleDeleteRelations($data, $relData);

        $this->getORM()
            ->mapper($this->targetTable)
            ->updateOne(
                $relData,
                null,
                true
            );
    }
}
