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
 * Interface CookiesInterface
 */
interface CookiesInterface
{
    public const SAMESITE_NONE = 'None';
    public const SAMESITE_LAX = 'Lax';
    public const SAMESITE_STRICT = 'Strict';

    public function set(string $name, string $value): bool;

    public function get(string $name): ?string;

    public function remove(string $name): bool;

    public function getStorage(): array;
}
