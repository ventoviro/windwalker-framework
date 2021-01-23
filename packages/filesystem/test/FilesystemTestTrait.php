<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filesystem\Test;

use Windwalker\Filesystem\Path;
use Windwalker\Filesystem\Path\PathLocator;

/**
 * Trait FilesystemTestTrait
 */
trait FilesystemTestTrait
{
    /**
     * assertPathEquals
     *
     * @param  mixed   $expect
     * @param  mixed   $actual
     * @param  string  $message
     *
     * @return  mixed
     */
    public static function assertPathEquals($expect, $actual, string $message = ''): mixed
    {
        return self::assertEquals(
            Path::clean($expect),
            Path::clean($actual),
            $message
        );
    }

    /**
     * assertPathEquals
     *
     * @param  mixed   $expect
     * @param  mixed   $actual
     * @param  string  $message
     *
     * @return  mixed
     */
    public static function assertRealpathEquals($expect, $actual, string $message = ''): mixed
    {
        return self::assertEquals(
            Path::normalize($expect),
            Path::normalize($actual),
            $message
        );
    }
}
