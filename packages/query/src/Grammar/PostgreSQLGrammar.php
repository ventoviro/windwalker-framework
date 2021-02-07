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

    // /**
    //  * @inheritDoc
    //  */
    // public function listTables(?string $schema = null): Query
    // {
    //     $query = $this->createQuery()
    //         ->select('table_name AS Name')
    //         ->from('information_schema.tables')
    //         ->where('table_type', 'BASE TABLE')
    //         ->order('table_name', 'ASC');
    //
    //     if ($schema) {
    //         $query->where('table_schema', $schema);
    //     } else {
    //         $query->whereNotIn('table_schema', ['pg_catalog', 'information_schema']);
    //     }
    //
    //     return $query;
    // }
    //
    // /**
    //  * @inheritDoc
    //  */
    // public function listViews(?string $schema = null): Query
    // {
    //     $query = $this->createQuery()
    //         ->select('table_name AS Name')
    //         ->from('information_schema.tables')
    //         ->where('table_type', 'VIEW')
    //         ->order('table_name', 'ASC');
    //
    //     if ($schema) {
    //         $query->where('table_schema', $schema);
    //     } else {
    //         $query->whereNotIn('table_schema', ['pg_catalog', 'information_schema']);
    //     }
    //
    //     return $query;
    // }
    //
    // /**
    //  * @inheritDoc
    //  */
    // public function dropTable(string $table, bool $ifExists = false, ...$options): Clause
    // {
    //     $options[] = 'CASCADE';
    //
    //     return parent::dropTable($table, $ifExists, ...$options);
    // }
}
