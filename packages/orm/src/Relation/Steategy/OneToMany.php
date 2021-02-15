<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Steategy;

use Windwalker\ORM\Relation\RelationProxies;
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
        $getter = fn () => $this->getORM()
            ->from($this->targetTable)
            ->where($this->createLoadConditions($data));

        RelationProxies::set($entity, $this->getPropName(), $getter);

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function save(array $data, object $entity): void
    {
    }

    /**
     * @inheritDoc
     */
    public function delete(array $data, object $entity): void
    {
    }
}
