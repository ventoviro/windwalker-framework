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
 * Interface HydratorAwareInterface
 */
interface HydratorAwareInterface
{
    /**
     * Get Hydrator object.
     *
     * @return  HydratorInterface
     */
    public function getHydrator(): HydratorInterface;
}
