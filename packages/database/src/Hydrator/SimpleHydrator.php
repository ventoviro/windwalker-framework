<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Database\Hydrator;

use Windwalker\Scalars\ArrayObject;
use Windwalker\Utilities\Contract\AccessibleInterface;
use Windwalker\Utilities\Contract\AccessorAccessibleInterface;
use Windwalker\Utilities\Contract\DumpableInterface;

/**
 * The SimpleHydrator class.
 */
class SimpleHydrator implements HydratorInterface
{
    /**
     * @inheritDoc
     */
    public function extract(object $object): array
    {
        if ($object instanceof DumpableInterface) {
            return (new AccessibleHydrator())->extract($object);
        }

        if ($object instanceof \Traversable) {
            return iterator_to_array($object);
        }

        return get_object_vars($object);
    }

    /**
     * @inheritDoc
     */
    public function hydrate(array $data, object $object): object
    {
        if ($object instanceof AccessorAccessibleInterface) {
            return (new AccessibleHydrator())->hydrate($data, $object);
        }

        if ($object instanceof \ArrayAccess) {
            foreach ($data as $key => $datum) {
                $object[$key] = $datum;
            }
        } else {
            foreach ($data as $key => $datum) {
                $object->$key = $datum;
            }
        }

        return $object;
    }
}
