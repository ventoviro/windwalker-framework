<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Language;

/**
 * Class LanguageNormalize
 *
 * @since 2.0
 */
abstract class LanguageNormalizer
{
    /**
     * toLanguageKey
     *
     * @param  string  $lang
     *
     * @return  string
     */
    public static function toBCP47(string $lang): string
    {
        return static::normalizeLangCode($lang, '-');
    }

    public static function toISO15897(string $lang): string
    {
        return static::normalizeLangCode($lang, '_');
    }

    public static function normalizeLangCode(string $lang, string $sep = '-'): string
    {
        $lang = str_replace(['_', '-'], $sep, $lang);

        $lang = explode($sep, $lang);

        if (isset($lang[1])) {
            $lang[1] = strtoupper($lang[1]);
        }

        $lang[0] = strtolower($lang[0]);

        return implode($sep, $lang);
    }

    /**
     * toLanguageKey
     *
     * @param  string  $key
     *
     * @return  string
     */
    public static function normalize(string $key): string
    {
        // Only allow A-Z a-z 0-9 and "_", other characters will be replace with "_".
        $key = preg_replace('/[^A-Z0-9]+/i', '.', $key);

        return strtolower(trim($key, '.'));
    }

    /**
     * shortLangCode
     *
     * @param  string  $code
     * @param  string  $separator
     *
     * @return  string
     *
     * @since  3.5.13
     */
    public static function shortLangCode(string $code, string $separator = '_'): string
    {
        [$first, $last] = static::extract($code);

        if ($last === null || (strtolower($first) === strtolower($last))) {
            return strtolower($first);
        }

        return strtolower($first) . $separator . strtoupper($last);
    }

    /**
     * extract
     *
     * @param  string  $code
     *
     * @return  array
     *
     * @since  3.5.13
     */
    public static function extract(string $code): array
    {
        $code = str_replace('_', '-', $code);

        return array_pad(explode('-', $code, 2), 2, null);
    }
}
