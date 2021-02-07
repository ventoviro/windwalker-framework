<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

/**
 * The MySQLGrammar class.
 */
class SQLiteGrammar extends BaseGrammar
{
    /**
     * @var string
     */
    public static string $name = 'SQLite';

    /**
     * @var array
     */
    public static array $nameQuote = ['`', '`'];
}
