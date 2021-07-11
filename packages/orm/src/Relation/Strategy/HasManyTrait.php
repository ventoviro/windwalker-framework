<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Strategy;

use ReflectionException;
use Windwalker\Data\Collection;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The HaasManyTrait class.
 */
trait HasManyTrait
{
    /**
     * diffChildren
     *
     * @param  array       $data
     * @param  object      $entity
     * @param  array|null  $oldData
     *
     * @return array
     *
     * @throws ReflectionException
     */
    public function diffRelated(array $data, object $entity, ?array $oldData): array
    {
        $collection = ReflectAccessor::getValue($entity, $this->getPropName())
            ?? $this->createCollection($data);

        $foreignMetadata = $this->getForeignMetadata();

        $attachEntities = null;
        $detachEntities = null;
        $keepEntities = null;

        if ($collection->isSync()) {
            $entities = $collection->all(Collection::class)->dump(true);

            if ($this->isFlush()) {
                // If is flush, let's delete all relations and make all attaches
                if ($oldData !== null) {
                    $this->deleteAllRelatives($oldData);
                }

                $attachEntities = $entities;
            } else {
                // If not flush let's make attach and detach diff
                $oldItems = $this->createCollection($oldData)
                    ->all(Collection::class)
                    ->dump(true);

                [$detachEntities,] = $this->getDetachDiff(
                    $entities,
                    $oldItems,
                    $foreignMetadata->getKeys(),
                    $data
                );
                [$attachEntities, $keepEntities] = $this->getAttachDiff(
                    $entities,
                    $oldItems,
                    $foreignMetadata->getKeys(),
                    $data
                );
            }
        } else {
            // Not sync, manually set attach/detach
            $attachEntities = $collection->getAttachedEntities();

            if ($this->isFlush()) {
                // If is flush, let's delete all relations and make all attaches
                if ($oldData !== null) {
                    $this->deleteAllRelatives($oldData);
                }
            } else {
                $detachEntities = $collection->getDetachedEntities();
            }
        }

        return [$attachEntities, $detachEntities, $keepEntities];
    }

    abstract public function attachEntities(iterable $entities, array $data): void;

    abstract public function detachEntities(iterable $entities, ?array $oldData): void;

    abstract public function changeEntities(iterable $entities, array $data, ?array $oldData): void;
}
