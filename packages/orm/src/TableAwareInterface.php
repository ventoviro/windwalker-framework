<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM;

/**
 * Interface TableAwareInterface
 */
interface TableAwareInterface
{
    /**
     * Get Table Name.
     *
     * @return  string
     */
    public static function table(): string;
}
