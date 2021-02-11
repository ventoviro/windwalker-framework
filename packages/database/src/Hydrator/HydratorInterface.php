<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Database\Hydrator;

/**
 * Interface HydratorInterface
 */
interface HydratorInterface
{
    /**
     * Hydrate object with the provided data.
     *
     * @param  array   $data
     * @param  object  $object
     *
     * @return  object
     */
    public function hydrate(array $data, object $object): object;

    /**
     * Extract to array.
     *
     * @param  object  $object
     *
     * @return  array
     */
    public function extract(object $object): array;
}
