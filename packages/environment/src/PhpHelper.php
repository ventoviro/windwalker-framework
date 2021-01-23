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
 * The PhpEnvironment class.
 *
 * @since  2.0
 */
class PhpHelper
{
    /**
     * isWeb
     *
     * @return  boolean
     */
    public static function isWeb(): bool
    {
        return in_array(
            PHP_SAPI,
            [
                'apache',
                'cgi',
                'fast-cgi',
                'srv',
            ]
        );
    }

    /**
     * isCli
     *
     * @return  boolean
     */
    public static function isCli(): bool
    {
        return in_array(
            PHP_SAPI,
            [
                'cli',
                'cli-server',
            ]
        );
    }

    /**
     * isHHVM
     *
     * @return  boolean
     */
    public static function isHHVM(): bool
    {
        return defined('HHVM_VERSION');
    }

    /**
     * isPHP
     *
     * @return  boolean
     */
    public static function isPHP(): bool
    {
        return !static::isHHVM();
    }

    /**
     * isEmbed
     *
     * @return  boolean
     */
    public static function isEmbed(): bool
    {
        return in_array(
            PHP_SAPI,
            [
                'embed',
            ]
        );
    }

    /**
     * Get PHP version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return PHP_VERSION;
    }

    /**
     * setStrict
     *
     * @return  void
     */
    public static function setStrict()
    {
        error_reporting(-1);
    }

    /**
     * setMuted
     *
     * @return  void
     */
    public static function setMuted()
    {
        error_reporting(0);
    }

    /**
     * Returns true when the runtime used is PHP and Xdebug is loaded.
     *
     * @return boolean
     */
    public function hasXdebug(): bool
    {
        return static::isPHP() && extension_loaded('xdebug');
    }

    /**
     * supportPcntl
     *
     * @return  boolean
     */
    public static function hasPcntl(): bool
    {
        return extension_loaded('PCNTL');
    }

    /**
     * supportCurl
     *
     * @return  boolean
     */
    public static function hasCurl(): bool
    {
        return function_exists('curl_init');
    }

    /**
     * supportMcrypt
     *
     * @return  boolean
     */
    public static function hasMcrypt(): bool
    {
        return extension_loaded('mcrypt');
    }
}
