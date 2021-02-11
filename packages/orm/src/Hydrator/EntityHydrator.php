<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Hydrator;

use Windwalker\Database\Hydrator\HydratorInterface;
use Windwalker\ORM\Attributes\Column;
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
        $metadata = $this->orm->getEntityMetadata($object);

        $item = [];

        /** @var \ReflectionAttribute $reflectColumn */
        foreach ($metadata->getReflectColumns() as [$prop, $reflectColumn]) {
            /** @var \ReflectionProperty $prop */
            /** @var Column $column */
            $column = $reflectColumn->newInstance();

            $colName  = $column->getName();
            $propName = $prop->getName();

            if (!array_key_exists($colName, $data)) {
                $colName = $propName;

                if (!array_key_exists($colName, $data)) {
                    continue;
                }
            }

            $value = $data[$colName];

            foreach ($metadata->getCastManager()->getFieldCasts($colName) as $cast) {
                $value = $this->orm->getAttributesResolver()
                    ->call(
                        $cast[0],
                        [
                            $value,
                            'orm' => $this->orm
                        ]
                    );
            }

            $item[$propName] = $value;
        }

        show($item);

        return $this->hydrator->hydrate($item, $object);
    }

    /**
     * @inheritDoc
     */
    public function extract(object $object): array
    {
        $item = $this->hydrator->extract($object);

        if ($object instanceof \stdClass) {
            return get_object_vars($object);
        }

        $metadata = $this->orm->getEntityMetadata($object);

        /** @var \ReflectionAttribute $reflectColumn */
        foreach ($metadata->getReflectColumns() as [$prop, $reflectColumn]) {
            /** @var \ReflectionProperty $prop */
            /** @var Column $column */
            $column = $reflectColumn->newInstance();

            $colName  = $column->getName();
            $propName = $prop->getName();

            if (!array_key_exists($propName, $item)) {
                $propName = $colName;

                if (!array_key_exists($propName, $item)) {
                    continue;
                }
            }

            $value = $item[$propName];
            $casts = $metadata->getCastManager()->getFieldCasts($colName);
            $casts = array_reverse($casts);

            foreach ($casts as $cast) {
                $value = $this->orm->getAttributesResolver()
                    ->call(
                        $cast[1],
                        [
                            $value,
                            'orm' => $this->orm
                        ]
                    );
            }

            $item[$colName] = $value;
        }

        return $item;
    }
}
