<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Hydrator;

use stdClass;
use Throwable;
use Windwalker\Database\Hydrator\FieldHydratorInterface;
use Windwalker\ORM\Attributes\Mapping;
use Windwalker\ORM\Exception\CastingException;
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

        $item = [];
        $props = $metadata->getProperties();
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

            $item[$propName] = static::castFieldForHydrate($metadata, $colName, $value);
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

        if ($object instanceof stdClass) {
            return get_object_vars($object);
        }

        $data = $this->hydrator->extract($object);

        $metadata = $this->orm->getEntityMetadata($object);
        $item = [];

        foreach ($metadata->getColumns() as $column) {
            $prop = $column->getProperty();

            $colName = $column->getName();
            $propName = $prop->getName();

            if (!array_key_exists($propName, $data)) {
                $propName = $colName;

                if (!array_key_exists($propName, $data)) {
                    continue;
                }
            }

            $value = $data[$propName];

            $value = static::castFieldForExtract($metadata, $colName, $value);

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

        if ($object instanceof stdClass) {
            return $object->$field;
        }

        $metadata = $this->orm->getEntityMetadata($object);

        $column = $metadata->getColumn($field);

        if (!$column) {
            $prop = $field;
        } else {
            $prop = $column->getProperty()->getName();
        }

        $value = $this->hydrator->extractField($object, $prop);

        return static::castFieldForExtract($metadata, $column->getName(), $value);
    }

    public static function castFieldForExtract(EntityMetadata $metadata, $colName, mixed $value)
    {
        if (!$metadata->getColumn($colName)) {
            return $value;
        }

        $casts = $metadata->getCastManager()->getFieldCasts($colName);
        $casts = array_reverse($casts);

        foreach ($casts as $cast) {
            try {
                $value = $metadata->getORM()->getAttributesResolver()
                    ->call(
                        $cast[1],
                        [
                            $value,
                            'orm' => $metadata->getORM(),
                        ]
                    );
            } catch (Throwable $e) {
                $castName = is_object($cast[1]) ? $cast[1]::class : json_encode($cast[1]);

                throw new CastingException(
                    sprintf(
                        'Error when extracting %s:%s to %s with value %s : %s',
                        $metadata->getClassName(),
                        $colName,
                        $castName,
                        get_debug_type($value),
                        $e->getMessage()
                    ),
                    $e->getCode(),
                    $e
                );
            }
        }

        return $value;
    }

    public static function castFieldForHydrate(EntityMetadata $metadata, $colName, mixed $value)
    {
        if (!$metadata->getColumn($colName)) {
            return $value;
        }

        $casts = $metadata->getCastManager()->getFieldCasts($colName);

        foreach ($casts as $cast) {
            try {
                $value = $metadata->getORM()->getAttributesResolver()
                    ->call(
                        $cast[0],
                        [
                            $value,
                            'orm' => $metadata->getORM(),
                        ]
                    );
            } catch (Throwable $e) {
                $castName = is_object($cast[0]) ? $cast[0]::class : json_encode($cast[0]);

                throw new CastingException(
                    sprintf(
                        'Error when hydrating %s:%s to %s with value %s : %s',
                        $metadata->getClassName(),
                        $colName,
                        $castName,
                        get_debug_type($value),
                        $e->getMessage()
                    ),
                    $e->getCode(),
                    $e
                );
            }
        }

        return $value;
    }

    /**
     * castArray
     *
     * @param  EntityMetadata  $metadata
     * @param  array           $data
     *
     * @return  array
     *
     * @internal
     */
    public static function castArray(EntityMetadata $metadata, array $data): array
    {
        foreach ($data as $k => $datum) {
            $data[$k] = static::castFieldForExtract($metadata, $k, $datum);
        }

        return $data;
    }
}
