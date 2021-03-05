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
use Windwalker\Database\Hydrator\FieldHydratorInterface;
use Windwalker\Database\Hydrator\HydratorInterface;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\Mapping;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;

/**
 * The EntityHydrator class.
 */
class EntityHydrator implements FieldHydratorInterface
{
    /**
     * EntityHydrator constructor.
     *
     * @param  FieldHydratorInterface  $hydrator
     * @param  ORM                     $orm
     */
    public function __construct(protected FieldHydratorInterface $hydrator, protected ORM $orm)
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
        $props   = $metadata->getProperties();
        $columns = $metadata->getColumns();

        foreach ($data as $colName => $value) {
            if ($column = $columns[$colName] ?? null) {
                if ($column instanceof Mapping && is_scalar($value)) {
                    continue;
                }

                $prop = $column->getProperty();
            } else {
                $prop = $props[$colName] ?? null;
            }

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

    /**
     * @inheritDoc
     */
    public function extractField(object $object, string $field): mixed
    {
        if (!EntityMetadata::isEntity($object)) {
            return $this->hydrator->extractField($object, $field);
        }

        if ($object instanceof \stdClass) {
            return $object->$field;
        }

        $metadata = $this->orm->getEntityMetadata($object);

        $column = $metadata->getColumn($field);

        if (!$column) {
            $prop = $field;
        } else {
            $prop = $column->getProperty()->getName();
        }

        return $this->hydrator->extractField($object, $prop);
    }
}
