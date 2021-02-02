<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities;

/**
 * The Env class.
 */
class Env
{
    /**
     * Get env.
     *
     * @param  string      $name
     * @param  mixed|null  $default
     *
     * @return  string|null
     */
    public static function get(string $name, mixed $default = null): ?string
    {
        return $_SERVER[$name] ?? $_ENV[$name] ?? ((string) $default);
    }
}
