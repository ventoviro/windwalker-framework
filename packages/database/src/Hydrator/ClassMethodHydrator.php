<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Database\Hydrator;

use LogicException;

/**
 * The ClassMethodHydrator class.
 *
 * @TODO: Implement more Hydrator and composition.
 */
class ClassMethodHydrator implements HydratorInterface
{
    /**
     * @inheritDoc
     */
    public function extract(object $object): array
    {
        throw new LogicException('This class not implemented yet.');
    }

    /**
     * @inheritDoc
     */
    public function hydrate(array $data, object $object): object
    {
        throw new LogicException('This class not implemented yet.');
    }
}
