<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Database\Hydrator;

use InvalidArgumentException;
use Windwalker\Scalars\ArrayObject;
use Windwalker\Utilities\Contract\AccessorAccessibleInterface;
use Windwalker\Utilities\Contract\DumpableInterface;

/**
 * The AccessibleHydrator class.
 */
class AccessibleHydrator implements HydratorInterface
{
    /**
     * @inheritDoc
     */
    public function extract(object $object): array
    {
        if (!$object instanceof DumpableInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s::extract() expects a %s object',
                    static::class,
                    DumpableInterface::class
                )
            );
        }

        return $object->dump();
    }

    /**
     * @inheritDoc
     */
    public function hydrate(array $data, object $object): object
    {
        if ($object instanceof ArrayObject) {
            return $object->fill($data, ['replace_nulls' => true]);
        }

        if ($object instanceof AccessorAccessibleInterface) {
            foreach ($data as $key => $datum) {
                $object->set($key, $datum);
            }

            return $object;
        }

        throw new InvalidArgumentException(
            sprintf(
                '%s::hydrate() expects an %s or %s',
                static::class,
                ArrayObject::class,
                AccessorAccessibleInterface::class
            )
        );
    }
}
