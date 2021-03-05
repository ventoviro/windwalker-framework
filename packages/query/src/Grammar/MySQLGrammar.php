<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;

use function Windwalker\Query\clause;
use function Windwalker\Query\qn;
use function Windwalker\Query\val;
use function Windwalker\raw;

/**
 * The MySQLGrammar class.
 */
class MySQLGrammar extends AbstractGrammar
{
    /**
     * @var string
     */
    protected static string $name = 'MySQL';

    /**
     * @var array
     */
    public static array $nameQuote = ['`', '`'];

    /**
     * @var string
     */
    public static string $nullDate = '1000-01-01 00:00:00';

    /**
     * If no connection set, we escape it with default function.
     *
     * Since mysql_real_escape_string() has been deprecated, we use an alternative one.
     * Please see:
     * http://stackoverflow.com/questions/4892882/mysql-real-escape-string-for-multibyte-without-a-connection
     *
     * @param  string  $text
     *
     * @return  string
     */
    public static function localEscape(string $text): string
    {
        return str_replace(
            ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
            ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
            $text
        );
    }

    /**
     * @inheritDoc
     */
    public function listViews(?string $schema = null): Query
    {
        $query = $this->createQuery()
            ->select('TABLE_NAME')
            ->from('INFORMATION_SCHEMA.TABLES')
            ->where('TABLE_TYPE', 'VIEW');

        if ($schema !== null) {
            $query->where('TABLE_SCHEMA', $schema);
        } else {
            $query->where('TABLE_SCHEMA', raw('(SELECT DATABASE())'));
        }

        return $query;
    }

    public function compileJsonSelector(Query $query, string $column, array $paths, bool $unQuoteLast = true): Clause
    {
        $expr = clause('JSON_EXTRACT()', [], ', ');

        $expr->append(qn($column, $query))
            ->append($vc = val('$.' . implode('.', $paths)));

        $query->bind(null, $vc);

        if ($unQuoteLast) {
            $expr = clause('JSON_UNQUOTE()', $expr, ', ');
        }

        return $expr;
    }
}
