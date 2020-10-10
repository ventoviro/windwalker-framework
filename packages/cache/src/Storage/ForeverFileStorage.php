<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

/**
 * The ForeverFileStorage class.
 */
class ForeverFileStorage extends FileStorage
{
    /**
     * isExpired
     *
     * @param  int       $expiration
     * @param  int|null  $time
     *
     * @return  bool
     */
    public static function isExpired(int $expiration, ?int $time = null): bool
    {
        return false;
    }
}
