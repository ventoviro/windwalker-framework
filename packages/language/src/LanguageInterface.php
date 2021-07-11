<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Language;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Interface LanguageInterface
 */
interface LanguageInterface
{
    /**
     * translate
     *
     * @param  string       $id
     * @param  string|null  $locale
     * @param  bool         $fallback
     *
     * @return  array
     */
    #[ArrayShape(['string', 'string'])]
    public function get(
        string $id,
        ?string $locale = null,
        bool $fallback = true
    ): array;

    public function trans(string $id, ...$args): string;

    public function choice(string $id, int|float $number, ...$args);

    public function replace(string $string, array $args = []): string;

    /**
     * has
     *
     * @param  string       $id
     * @param  string|null  $locale
     * @param  bool         $fallback
     *
     * @return  bool
     *
     * @since  3.5.2
     */
    public function has(string $id, ?string $locale = null, bool $fallback = true): bool;
}
