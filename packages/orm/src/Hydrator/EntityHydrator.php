<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Hydrator;

use Windwalker\Attributes\AttributesResolver;
use Windwalker\Database\Hydrator\HydratorInterface;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;

/**
 * The EntityHydrator class.
 */
class EntityHydrator implements HydratorInterface
{
    /**
     * EntityHydrator constructor.
     *
     * @param  HydratorInterface  $hydrator
     * @param  ORM                $orm
     */
    public function __construct(protected HydratorInterface $hydrator, protected ORM $orm)
    {
    }

    /**
     * @inheritDoc
     */
    public function hydrate(array $data, object $object): object
    {
        if (!EntityMetadata::isEntity($object)) {
            return $this->hydrator->hydrate($data, $object);
        }

        $metadata = $this->orm->getEntityMetadata($object);

        $item    = [];
        $columns = [];
        $props   = [];

        foreach ($metadata->getProperties() as $prop) {
            $props[$prop->getName()] = $prop;
            $column = $metadata->getColumnByPropertyName($prop->getName());

            if ($column) {
                $columns[$column->getName()] = $prop;
            }
        }

        foreach ($data as $colName => $value) {
            $prop = $columns[$colName] ?? $props[$colName] ?? null;

            if (!$prop) {
                $item[$colName] = $value;
                continue;
            }

            $propName = $prop->getName();

            foreach ($metadata->getCastManager()->getFieldCasts($colName) as $cast) {
                $value = $this->orm->getAttributesResolver()
                    ->call(
                        $cast[0],
                        [
                            $value,
                            'orm' => $this->orm,
                        ]
                    );
            }

            $item[$propName] = $value;
        }

        return $this->hydrator->hydrate($item, $object);
    }

    /**
     * @inheritDoc
     */
    public function extract(object $object): array
    {
        if (!EntityMetadata::isEntity($object)) {
            return $this->hydrator->extract($object);
        }

        if ($object instanceof \stdClass) {
            return get_object_vars($object);
        }

        $data = $this->hydrator->extract($object);

        $metadata = $this->orm->getEntityMetadata($object);
        $item = [];

        foreach ($metadata->getColumns() as $column) {
            $prop = $column->getProperty();

            $colName  = $column->getName();
            $propName = $prop->getName();

            if (!array_key_exists($propName, $data)) {
                $propName = $colName;

                if (!array_key_exists($propName, $data)) {
                    continue;
                }
            }

            $value = $data[$propName];
            $casts = $metadata->getCastManager()->getFieldCasts($colName);
            $casts = array_reverse($casts);

            foreach ($casts as $cast) {
                $value = $this->orm->getAttributesResolver()
                    ->call(
                        $cast[1],
                        [
                            $value,
                            'orm' => $this->orm,
                        ]
                    );
            }

            $item[$colName] = $value;
        }

        return $item;
    }
}
