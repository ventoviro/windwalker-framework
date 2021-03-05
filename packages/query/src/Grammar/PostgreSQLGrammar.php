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
 * The PostgresqlGrammar class.
 */
class PostgreSQLGrammar extends AbstractGrammar
{
    /**
     * @var string
     */
    public static string $name = 'PostgreSQL';

    /**
     * @var string
     */
    public static string $nullDate = '1970-01-01 00:00:00';

    /**
     * @inheritDoc
     */
    public function compileLimit(Query $query, array $sql): array
    {
        $limit  = (int) $query->getLimit();
        $offset = (int) $query->getOffset();

        if ($limit > 0) {
            $sql['limit'] = 'LIMIT ' . $limit;
        }

        if ($offset > 0) {
            $sql['offset'] = 'OFFSET ' . $offset;
        }

        return $sql;
    }

    public function compileJsonSelector(Query $query,
        string $column,
        array $paths,
        bool $unQuoteLast = true,
        bool $instant = false
    ): Clause
    {
        $newPaths = [];

        foreach ($paths as $path) {
            preg_match('/([\w.]+)\[(\d)\]/', $path, $matches);

            if (count($matches) >= 3) {
                $newPaths[] = $query->quoteOrVal($matches[1], $instant);
                $newPaths[] = (int) $matches[2];
            } else {
                $newPaths[] = $query->quoteOrVal($path, $instant);
            }
        }

        $last = array_pop($newPaths);
        $lastArrow = $unQuoteLast ? '->>' : '->';
        array_unshift($newPaths, qn($column, $query));

        return clause(
            '',
            [clause('', $newPaths, '->'), $last],
            $lastArrow
        );
    }
}
