<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Database\Hydrator;

use ArrayAccess;
use stdClass;
use Traversable;
use Windwalker\Utilities\Contract\AccessorAccessibleInterface;
use Windwalker\Utilities\Contract\DumpableInterface;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The SimpleHydrator class.
 */
class SimpleHydrator implements HydratorInterface, FieldHydratorInterface
{
    /**
     * @inheritDoc
     */
    public function extract(object $object): array
    {
        if ($object instanceof DumpableInterface) {
            return (new AccessibleHydrator())->extract($object);
        }

        if ($object instanceof Traversable) {
            return iterator_to_array($object);
        }

        return ReflectAccessor::getPropertiesValues($object);
    }

    /**
     * @inheritDoc
     */
    public function hydrate(array $data, object $object): object
    {
        if ($object instanceof AccessorAccessibleInterface) {
            return (new AccessibleHydrator())->hydrate($data, $object);
        }

        if ($object instanceof ArrayAccess) {
            foreach ($data as $key => $datum) {
                $object[$key] = $datum;
            }
        } elseif ($object instanceof stdClass) {
            foreach ($data as $key => $datum) {
                $object->$key = $datum;
            }
        } else {
            foreach ($data as $key => $datum) {
                ReflectAccessor::setValue($object, $key, $datum, true);
            }
        }

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function extractField(object $object, string $field): mixed
    {
        if ($object instanceof AccessorAccessibleInterface) {
            return $object->$field;
        }

        if (ReflectAccessor::hasProperty($object, $field)) {
            return ReflectAccessor::getValue($object, $field);
        }

        if ($object instanceof Traversable) {
            return iterator_to_array($object)[$field] ?? null;
        }

        return null;
    }
}
