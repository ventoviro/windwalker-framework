<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Strategy;

use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The OneToOne class.
 */
class OneToOne extends AbstractRelation
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
    public function save(array $data, object $entity, ?array $oldData = null): void
    {
        if ($this->onUpdate === Action::NO_ACTION || $this->onUpdate === Action::RESTRICT) {
            return;
        }

        // Get foreign entity
        $foreignEntity = ReflectAccessor::getValue($entity, $this->getPropName());

        // If no foreign entity exists but on update is CASCADE
        // try load it once.
        if ($foreignEntity === null && $this->onUpdate === Action::CASCADE) {
            $foreignEntity = RelationProxies::call($entity, $this->getPropName());
        }

        // If still no any relation found, return.
        if ($foreignEntity === null) {
            return;
        }

        $foreignData = $this->getORM()->extractEntity($foreignEntity);
        $foreignData = $this->handleUpdateRelations($data, $foreignData);
        $foreignData = $this->mergeMorphValues($foreignData);

        if ($this->isFlush()) {
            $this->deleteAllRelatives($foreignData);
            $foreignData = $this->clearKeysValues($foreignData);
        }

        $this->getORM()
            ->mapper($this->targetTable)
            ->saveOne(
                $foreignData,
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
        $foreignEntity = ReflectAccessor::getValue($entity, $this->getPropName())
            ?? RelationProxies::call($entity, $this->getPropName());

        if ($foreignEntity === null) {
            return;
        }

        $foreignData = $this->getORM()->extractEntity($foreignEntity);
        $foreignData = $this->clearRelativeFields($foreignData);

        $this->getORM()
            ->mapper($this->targetTable)
            ->updateOne(
                $foreignData,
                null,
                true
            );
    }
}
