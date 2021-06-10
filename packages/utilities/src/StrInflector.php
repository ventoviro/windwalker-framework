<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities;

use DomainException;
use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\String\Inflector\InflectorInterface;

/**
 * The StrInflector class.
 */
class StrInflector
{
    public static ?InflectorInterface $inflector = null;

    /**
     * Checks if a word is in a plural form.
     *
     * @param  string  $word  The string input.
     *
     * @return  bool  True if word is plural, false if not.
     *
     * @since  2.0
     */
    public static function isPlural(string $word): bool
    {
        // Compute the inflection to cache the values, and compare.
        return static::toPlural(static::toSingular($word)) === $word;
    }

    /**
     * Checks if a word is in a singular form.
     *
     * @param  string  $word  The string input.
     *
     * @return  bool  True if word is singular, false if not.
     *
     * @since  2.0
     */
    public static function isSingular(string $word): bool
    {
        // Compute the inflection to cache the values, and compare.
        return static::toSingular($word) === $word;
    }

    /**
     * Converts a word into its plural form.
     *
     * @param  string  $word  The singular word to pluralize.
     *
     * @return  string  An inflected string, or false if no rule could be applied.
     *
     * @since  2.0
     */
    public static function toPlural(string $word): string
    {
        $words = static::getPossiblePluralize($word);

        return array_pop($words);
    }

    /**
     * Get possible pluralize words.
     *
     * @param  string  $word  The singular word to pluralize.
     *
     * @return  array
     */
    public static function getPossiblePluralize(string $word): array
    {
        static::checkDependency();

        return static::getInflector()->pluralize($word);
    }

    /**
     * Converts a word into its singular form.
     *
     * @param  string  $word  The plural word to singularise.
     *
     * @return  string  An inflected string, or false if no rule could be applied.
     *
     * @since  2.0
     */
    public static function toSingular(string $word): string
    {
        $words = static::getPossibleSingularize($word);

        return array_pop($words);
    }

    /**
     * Get possible singularize words.
     *
     * @param  string  $word  The plural word to singularize.
     *
     * @return  array
     */
    public static function getPossibleSingularize(string $word): array
    {
        static::checkDependency();

        return static::getInflector()->singularize($word);
    }

    protected static function checkDependency(): void
    {
        if (!interface_exists(InflectorInterface::class)) {
            throw new DomainException('Please install symfony/string first');
        }
    }

    protected static function getInflector(): InflectorInterface
    {
        if (!static::$inflector) {
            static::$inflector = new EnglishInflector();
        }

        return static::$inflector;
    }
}
