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

/**
 * The OracleGrammar class.
 */
class OracleGrammar extends BaseGrammar
{
    /**
     * @var string
     */
    public static string $name = 'Oracle';

    /**
     * @var string
     */
    public static string $nullDate = 'RRRR-MM-DD HH24:MI:SS';

    /**
     * @inheritDoc
     */
    public function compileLimit(Query $query, array $sql): array
    {
        $limit  = (int) $query->getLimit();
        $offset = (int) $query->getOffset();

        // Check if we need to mangle the query.
        if ($limit || $offset) {
            $start = 'SELECT windwalker2.*
                      FROM (
                          SELECT windwalker1.*, ROWNUM AS windwalker_db_rownum
                          FROM (';

            $end = ') windwalker1
            ) windwalker2';

            // Check if the limit value is greater than zero.
            if ($limit > 0) {
                $end .= ' WHERE windwalker2.windwalker_db_rownum BETWEEN '
                    . ($offset + 1) . ' AND ' . ($offset + $limit);
            } elseif ($offset) {
                $end .= ' WHERE windwalker2.windwalker_db_rownum > ' . ($offset + 1);
            }

            $sql = array_merge(
                ['rownum_start' => $start],
                $sql,
                ['rownum_end' => $end]
            );
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
        throw new \LogicException('This DB does not support JSON.');
    }
}
