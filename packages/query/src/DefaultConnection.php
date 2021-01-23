<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query;

use Windwalker\Query\Grammar\AbstractGrammar;

/**
 * The DefaultConnection class.
 */
class DefaultConnection
{
    protected static $escaper;

    /**
     * @var AbstractGrammar
     */
    protected static $grammar;

    /**
     * @return mixed
     */
    public static function getEscaper(): mixed
    {
        return static::$escaper;
    }

    /**
     * @param  mixed  $escaper
     *
     * @return  void
     */
    public static function setEscaper(mixed $escaper): void
    {
        static::$escaper = $escaper;
    }

    /**
     * @return AbstractGrammar
     */
    public static function getGrammar(): ?AbstractGrammar
    {
        return static::$grammar;
    }

    /**
     * @param  AbstractGrammar  $grammar
     *
     * @return  void
     */
    public static function setGrammar(AbstractGrammar $grammar)
    {
        static::$grammar = $grammar;
    }
}
