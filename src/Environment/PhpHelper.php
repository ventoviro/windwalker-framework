<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Environment;

/**
 * The PhpEnvironment class.
 *
 * @since  2.0
 */
class PhpHelper
{
    /**
     * isWeb.
     *
     * @return bool
     */
    public static function isWeb()
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
     * isCli.
     *
     * @return bool
     */
    public static function isCli()
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
     * isHHVM.
     *
     * @return bool
     */
    public static function isHHVM()
    {
        return defined('HHVM_VERSION');
    }

    /**
     * isPHP.
     *
     * @return bool
     */
    public static function isPHP()
    {
        return !static::isHHVM();
    }

    /**
     * isEmbed.
     *
     * @return bool
     */
    public static function isEmbed()
    {
        return in_array(
            PHP_SAPI,
            [
                'embed',
            ]
        );
    }

    /**
     * Get PHP version.
     *
     * @return string
     */
    public function getVersion()
    {
        if (static::isHHVM()) {
            return HHVM_VERSION;
        } else {
            return PHP_VERSION;
        }
    }

    /**
     * setStrict.
     *
     * @return void
     */
    public static function setStrict()
    {
        error_reporting(-1);
    }

    /**
     * setMuted.
     *
     * @return void
     */
    public static function setMuted()
    {
        error_reporting(0);
    }

    /**
     * Returns true when the runtime used is PHP and Xdebug is loaded.
     *
     * @return bool
     */
    public function hasXdebug()
    {
        return static::isPHP() && extension_loaded('xdebug');
    }

    /**
     * supportPcntl.
     *
     * @return bool
     */
    public static function hasPcntl()
    {
        return extension_loaded('PCNTL');
    }

    /**
     * supportCurl.
     *
     * @return bool
     */
    public static function hasCurl()
    {
        return function_exists('curl_init');
    }

    /**
     * supportMcrypt.
     *
     * @return bool
     */
    public static function hasMcrypt()
    {
        return extension_loaded('mcrypt');
    }
}
