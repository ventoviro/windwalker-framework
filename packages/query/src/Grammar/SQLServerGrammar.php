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
use function Windwalker\Query\expr;
use function Windwalker\Query\qn;
use function Windwalker\Query\val;

/**
 * The SqlsrvGrammar class.
 */
class SQLServerGrammar extends AbstractGrammar
{
    /**
     * @var string
     */
    public static string $name = 'SQLServer';

    /**
     * @var array
     */
    public static array $nameQuote = ['[', ']'];

    /**
     * @var string
     */
    public static string $nullDate = '1900-01-01 00:00:00';

    public function compileInsert(Query $query): string
    {
        $sql['insert'] = $query->getInsert();

        if ($set = $query->getSet()) {
            $sql['set'] = $set;
        } else {
            if ($columns = $query->getColumns()) {
                $sql['columns'] = $columns;
            }

            if ($values = $query->getValues()) {
                $sql['values'] = $values;
            }

            if ($query->getIncrementField()) {
                $elements = $sql['insert']->getElements();
                $table    = $elements[array_key_first($elements)];

                $sql = array_merge(
                    ['id_insert_on' => sprintf('SET IDENTITY_INSERT %s ON;', $table)],
                    $sql,
                    ['id_insert_off' => sprintf('; SET IDENTITY_INSERT %s OFF;', $table)]
                );
            }
        }

        return trim(implode(' ', $sql));
    }

    public function compileLimit(Query $query, array $sql): array
    {
        $limit  = $query->getLimit();
        $offset = (int) $query->getOffset();

        $q = implode(' ', $sql);

        if ($limit !== null) {
            $total = $offset + $limit;

            $position = stripos($q, 'SELECT');
            $distinct = stripos($q, 'SELECT DISTINCT');

            if ($position === $distinct) {
                $q = substr_replace($q, 'SELECT DISTINCT TOP ' . (int) $total, $position, 15);
            } else {
                $q = substr_replace($q, 'SELECT TOP ' . (int) $total, $position, 6);
            }
        }

        if (!$offset) {
            return [$q];
        }

        return array_merge(
            ['row_number' => 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS RowNumber FROM ('],
            [$q],
            ['end_row_number' => ') AS A) AS A WHERE RowNumber > ' . (int) $offset]
        );
    }

    public function compileJsonSelector(Query $query,
        string $column,
        array $paths,
        bool $unQuoteLast = true,
        bool $instant = false
    ): Clause
    {
        $vc = $query->quoteOrVal('$.' . implode('.', $paths), $instant);

        $expr = $unQuoteLast
            ? expr('JSON_VALUE()', $vc)
            : expr('JSON_QUERY()', $vc);

        return $expr->prepend(qn($column, $query));
    }

    /**
     * If no connection set, we escape it with default function.
     *
     * @param  string  $text
     *
     * @return  string
     */
    public static function localEscape(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        if (is_numeric($text)) {
            return $text;
        }

        $nonDisplayables = [
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',             // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/',                   // 11
            '/\x0c/',                   // 12
            '/[\x0e-\x1f]/'             // 14-31
        ];

        foreach ($nonDisplayables as $regex) {
            $text = preg_replace($regex, '', $text);
        }

        return str_replace("'", "''", $text);
    }

    /**
     * @inheritDoc
     */
    public function listDatabases($where = null): Query
    {
        return $this->createQuery()
            ->select('name')
            ->from('master.dbo.sysdatabases');
    }

    /**
     * @inheritDoc
     */
    public function listTables(?string $schema = null): Query
    {
        $query = $this->createQuery()
            ->select('TABLE_NAME')
            ->from('INFORMATION_SCHEMA.TABLES')
            ->where('TABLE_TYPE', 'BASE TABLE');

        if ($schema !== null) {
            $query->where('TABLE_CATALOG', $schema);
        } else {
            $query->where('TABLE_SCHEMA', '!=', 'INFORMATION_SCHEMA');
        }

        return $query;
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
            $query->where('TABLE_CATALOG', $schema);
        } else {
            $query->where('TABLE_SCHEMA', '!=', 'INFORMATION_SCHEMA');
        }

        return $query;
    }

    /**
     * dropTable
     *
     * @param  string  $table
     * @param  bool    $ifExists
     * @param  mixed   ...$options
     *
     * @return  string
     */
    public function dropTable(string $table, bool $ifExists = false, ...$options): Clause
    {
        // Drop all foreign key reference to this table
        // @see https://social.msdn.microsoft.com/Forums/sqlserver/en-US/219f8a19-0026-49a1-a086-11c5d57d9c97/tsql-to-drop-all-constraints?forum=transactsql
        $dropFK = <<<SQL
declare @str varchar(max)
declare cur cursor for

    SELECT 'ALTER TABLE ' + '[' + s.name + '].[' + t.name + '] DROP CONSTRAINT ['+ f.name + ']'
    FROM sys.foreign_keys AS f
    LEFT JOIN sys.objects AS t ON f.parent_object_id = t.object_id
    LEFT JOIN sys.schemas AS s ON t.schema_id = s.schema_id
    WHERE s.name = 'dbo' AND f.referenced_object_id = object_id(%q)
    ORDER BY t.type

open cur
FETCH NEXT FROM cur INTO @str
WHILE (@@fetch_status = 0) BEGIN
    PRINT @str
    EXEC (@str)
    FETCH NEXT FROM cur INTO @str
END

close cur
deallocate cur;
SQL;

        return static::build(
            $this->createQuery()->format(
                $dropFK,
                $table
            ),
            'DROP TABLE',
            $ifExists ? 'IF EXISTS' : null,
            self::quoteName($table),
            ...$options
        );
    }
}
