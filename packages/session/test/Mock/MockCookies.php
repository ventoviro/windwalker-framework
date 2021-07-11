<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Test\Mock;

use Windwalker\Session\Cookie\Cookies;

/**
 * The MockCookieSetter class.
 */
class MockCookies extends Cookies
{
    public array $cookies = [];

    public array $cookieData = [];

    /**
     * set
     *
     * @param  string  $name
     * @param  string  $value
     *
     * @return  bool
     */
    public function set(string $name, string $value): bool
    {
        $this->cookies[$name] = $value;

        $opt = $this->getOptions();
        $opt['value'] = $value;
        $this->cookieData[$name] = $opt;

        return true;
    }

    public function clear(): void
    {
        $this->cookies = [];
        $this->cookieData = [];
    }
}
