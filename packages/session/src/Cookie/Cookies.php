<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

/**
 * The CookieSetter class.
 */
class Cookies extends AbstractCookies
{
    public static function create(): static
    {
        return new static();
    }

    public function set(string $name, string $value): bool
    {
        if (headers_sent()) {
            return false;
        }

        return setcookie($name, $value, $this->getOptions());
    }

    public function get(string $name): ?string
    {
        return $_COOKIE[$name] ?? null;
    }

    public function remove(string $name): bool
    {
        return setcookie($name, '', ['expires' => time() - 500]);
    }

    public function getStorage(): array
    {
        return $_COOKIE;
    }
}
