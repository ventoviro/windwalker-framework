<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker;

use JetBrains\PhpStorm\Pure;
use Swoole\Coroutine;

if (!function_exists('Windwalker\go')) {
    /**
     * go
     *
     * @param  callable    $handler
     * @param  array|null  $params
     *
     * @return  mixed
     */
    function go(callable $handler, $params = null): mixed
    {
        if (swoole_installed()) {
            return \go($handler, $params);
        }

        return $handler();
    }
}

if (!function_exists('Windwalker\run')) {
    /**
     * go
     *
     * @param  callable    $handler
     * @param  array|null  $params
     *
     * @return  mixed
     */
    function run(callable $handler, $params = null): mixed
    {
        if (swoole_installed()) {
            return Coroutine\run($handler, $params);
        }

        return $handler();
    }
}

if (!function_exists('Windwalker\swoole_in_coroutine')) {
    function swoole_in_coroutine(): bool
    {
        if (!swoole_installed()) {
            return false;
        }

        if (Coroutine::getPcid() === false) {
            return false;
        }

        return true;
    }
}

if (!function_exists('Windwalker\swoole_installed')) {
    /**
     * swoole_installed
     *
     * @return  bool
     */
    #[Pure]
    function swoole_installed(): bool
    {
        return extension_loaded('swoole');
    }
}
