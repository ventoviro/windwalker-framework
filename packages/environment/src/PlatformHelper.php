<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Environment;

/**
 * The ServerHelper class.
 *
 * @since  2.0
 */
class PlatformHelper
{
    /**
     * Property server.
     *
     * @var Platform|null
     */
    protected static ?Platform $platform = null;

    /**
     * isWindows
     *
     * @return  boolean
     */
    public static function isWindows(): bool
    {
        return static::getPlatform()->isWindows();
    }

    /**
     * isLinux
     *
     * @return  boolean
     */
    public static function isLinux(): bool
    {
        return static::getPlatform()->isLinux();
    }

    /**
     * isUnix
     *
     * @return  boolean
     */
    public static function isUnix(): bool
    {
        return static::getPlatform()->isUnix();
    }

    /**
     * getServer
     *
     * @return  Platform
     */
    public static function getPlatform(): Platform
    {
        if (!static::$platform) {
            static::$platform = new Platform();
        }

        return static::$platform;
    }

    /**
     * Method to set property server
     *
     * @param  Platform  $platform
     *
     * @return  void
     */
    public static function setPlatform(Platform $platform): void
    {
        static::$platform = $platform;
    }
}
