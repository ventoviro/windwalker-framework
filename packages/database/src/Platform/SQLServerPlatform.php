<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Platform;

use LogicException;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Schema;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;

use function Windwalker\raw;

/**
 * The SqlserverPlatform class.
 */
class SQLServerPlatform extends AbstractPlatform
{
    protected string $name = self::SQLSERVER;

    protected static ?string $defaultSchema = 'dbo';

    public function listDatabasesQuery(): Query
    {
        return $this->db->createQuery()
            ->select('name')
            ->from('master.dbo.sysdatabases');
    }

    public function listSchemaQuery(): Query
    {
        return $this->db->createQuery()
            ->select('SCHEMA_NAME')
            ->from('INFORMATION_SCHEMA.SCHEMATA')
            ->where('SCHEMA_NAME', '!=', 'INFORMATION_SCHEMA');
    }

    public function listTablesQuery(?string $schema): Query
    {
        return $this->createQuery()
            ->select(
                [
                    'TABLE_NAME',
                    'TABLE_SCHEMA',
                    'TABLE_TYPE',
                    raw('NULL AS VIEW_DEFINITION'),
                    raw('NULL AS CHECK_OPTION'),
                    raw('NULL AS IS_UPDATABLE'),
                ]
            )
            ->from('INFORMATION_SCHEMA.TABLES')
            ->where('TABLE_TYPE', 'BASE TABLE')
            ->tap(
                static function (Query $query) use ($schema) {
                    if ($schema !== null) {
                        $query->where('TABLE_SCHEMA', $schema);
                    } else {
                        $query->where('TABLE_SCHEMA', '!=', 'INFORMATION_SCHEMA');
                    }
                }
            )
            ->order('TABLE_NAME');
    }

    public function listViewsQuery(?string $schema): Query
    {
        return $this->createQuery()
            ->select(
                [
                    'TABLE_NAME',
                    'TABLE_SCHEMA',
                    raw('\'VIEW\' AS TABLE_TYPE'),
                    'VIEW_DEFINITION',
                    'CHECK_OPTION',
                    'IS_UPDATABLE',
                ]
            )
            ->from('INFORMATION_SCHEMA.VIEWS')
            ->tap(
                static function (Query $query) use ($schema) {
                    if ($schema !== null) {
                        $query->where('TABLE_SCHEMA', $schema);
                    } else {
                        $query->where('TABLE_SCHEMA', '!=', 'INFORMATION_SCHEMA');
                    }
                }
            )
            ->order('TABLE_NAME');
    }

    public function listColumnsQuery(string $table, ?string $schema): Query
    {
        return $this->createQuery()
            ->select(
                [
                    'c.ORDINAL_POSITION',
                    'c.COLUMN_DEFAULT',
                    'c.IS_NULLABLE',
                    'c.DATA_TYPE',
                    'c.CHARACTER_MAXIMUM_LENGTH',
                    'c.CHARACTER_OCTET_LENGTH',
                    'c.NUMERIC_PRECISION',
                    'c.NUMERIC_SCALE',
                    'c.COLUMN_NAME',
                    'sc.is_identity',
                ]
            )
            ->from('INFORMATION_SCHEMA.COLUMNS', 'c')
            ->leftJoin(
                'sys.columns',
                'sc',
                [
                    ['sc.object_id', '=', raw('object_id(c.TABLE_NAME)')],
                    ['sc.name', '=', 'c.COLUMN_NAME'],
                ]
            )
            ->where('TABLE_NAME', $this->db->replacePrefix($table))
            ->tap(
                static function (Query $query) use ($schema) {
                    if ($schema !== null) {
                        $query->where('TABLE_SCHEMA', $schema);
                    } else {
                        $query->where('TABLE_SCHEMA', '!=', 'INFORMATION_SCHEMA');
                    }
                }
            );
    }

    public function listConstraintsQuery(string $table, ?string $schema): Query
    {
        return $this->createQuery()
            ->select(
                [
                    'T.TABLE_NAME',
                    'TC.CONSTRAINT_NAME',
                    'TC.CONSTRAINT_TYPE',
                    'KCU.COLUMN_NAME',
                    'CC.CHECK_CLAUSE',
                    'RC.MATCH_OPTION',
                    'RC.UPDATE_RULE',
                    'RC.DELETE_RULE',
                    'KCU2.TABLE_SCHEMA AS REFERENCED_TABLE_SCHEMA',
                    'KCU2.TABLE_NAME AS REFERENCED_TABLE_NAME',
                    'KCU2.COLUMN_NAME AS REFERENCED_COLUMN_NAME',
                ]
            )
            ->from('INFORMATION_SCHEMA.TABLES', 'T')
            ->innerJoin(
                'INFORMATION_SCHEMA.TABLE_CONSTRAINTS',
                'TC',
                [
                    ['T.TABLE_SCHEMA', '=', 'TC.TABLE_SCHEMA'],
                    ['T.TABLE_NAME', '=', 'TC.TABLE_NAME'],
                ]
            )
            ->leftJoin(
                'INFORMATION_SCHEMA.KEY_COLUMN_USAGE',
                'KCU',
                [
                    ['KCU.TABLE_SCHEMA', '=', 'TC.TABLE_SCHEMA'],
                    ['KCU.TABLE_NAME', '=', 'TC.TABLE_NAME'],
                    ['KCU.CONSTRAINT_NAME', '=', 'TC.CONSTRAINT_NAME'],
                ]
            )
            ->leftJoin(
                'INFORMATION_SCHEMA.CHECK_CONSTRAINTS',
                'CC',
                [
                    ['CC.CONSTRAINT_SCHEMA', '=', 'TC.CONSTRAINT_SCHEMA'],
                    ['CC.CONSTRAINT_NAME', '=', 'TC.CONSTRAINT_NAME'],
                ]
            )
            ->leftJoin(
                'INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS',
                'RC',
                [
                    ['RC.CONSTRAINT_SCHEMA', '=', 'TC.CONSTRAINT_SCHEMA'],
                    ['RC.CONSTRAINT_NAME', '=', 'TC.CONSTRAINT_NAME'],
                ]
            )
            ->leftJoin(
                'INFORMATION_SCHEMA.KEY_COLUMN_USAGE',
                'KCU2',
                [
                    ['RC.UNIQUE_CONSTRAINT_SCHEMA', '=', 'KCU2.CONSTRAINT_SCHEMA'],
                    ['RC.UNIQUE_CONSTRAINT_NAME', '=', 'KCU2.CONSTRAINT_NAME'],
                    ['KCU.ORDINAL_POSITION', '=', 'KCU2.ORDINAL_POSITION'],
                ]
            )
            ->where('T.TABLE_NAME', $this->db->replacePrefix($table))
            ->where('T.TABLE_TYPE', 'IN', ['BASE table', 'VIEW'])
            ->tap(
                static function (Query $query) use ($schema) {
                    if ($schema !== null) {
                        $query->where('T.TABLE_SCHEMA', $schema);
                    } else {
                        $query->whereNotIn('T.TABLE_SCHEMA', ['PG_CATALOG', 'INFORMATION_SCHEMA']);
                    }

                    $order = 'CASE %n'
                        . " WHEN 'PRIMARY KEY' THEN 1"
                        . " WHEN 'UNIQUE' THEN 2"
                        . " WHEN 'FOREIGN KEY' THEN 3"
                        . " WHEN 'CHECK' THEN 4"
                        . ' ELSE 5 END'
                        . ', %n'
                        . ', %n';

                    $query->order(
                        $query->raw(
                            $order,
                            'TC.CONSTRAINT_TYPE',
                            'TC.CONSTRAINT_NAME',
                            'KCU.ORDINAL_POSITION'
                        )
                    );
                }
            );
    }

    public function listIndexesQuery(string $table, ?string $schema): Query
    {
        return $this->createQuery()
            ->selectRaw('schema_name(tbl.schema_id) AS schema_name')
            ->select(
                [
                    'tbl.name AS table_name',
                    'col.name AS column_name',
                    'idx.name AS index_name',
                    'col.*',
                    'idx.*',
                ]
            )
            ->from('sys.columns AS col')
            ->leftJoin(
                'sys.tables',
                'tbl',
                'col.object_id',
                '=',
                'tbl.object_id'
            )
            ->leftJoin(
                'sys.index_columns',
                'ic',
                [
                    ['col.column_id', '=', 'ic.column_id'],
                    ['ic.object_id', '=', 'tbl.object_id'],
                ]
            )
            ->leftJoin(
                'sys.indexes',
                'idx',
                [
                    ['idx.object_id', '=', 'tbl.object_id'],
                    ['idx.index_id', '=', 'ic.index_id'],
                ]
            )
            ->where('tbl.name', $this->db->replacePrefix($table))
            ->orWhere(
                function (Query $query) {
                    $query->where('idx.name', '!=', null);
                    $query->where('col.is_identity', 1);
                    $query->where('idx.is_primary_key', 1);
                }
            );
    }

    public function dropColumn(string $table, string $name, ?string $schema = null): StatementInterface
    {
        $this->dropColumnConstraints($table, $name, $schema);

        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'DROP COLUMN',
                $this->db->quoteName($name),
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function listColumns(string $table, ?string $schema = null): array
    {
        $columns = [];

        foreach ($this->loadColumnsStatement($table, $schema) as $row) {
            if ($row['COLUMN_DEFAULT'] !== null) {
                $default = preg_replace(
                    "/(^(\(\(|\('|\(N'|\()|(('\)|(?<!\()\)\)|\))$))/i",
                    '',
                    (string) $row['COLUMN_DEFAULT']
                );
            } else {
                $default = null;
            }

            $columns[$row['COLUMN_NAME']] = [
                'column_name' => $row['COLUMN_NAME'],
                'ordinal_position' => $row['ORDINAL_POSITION'],
                'column_default' => $default,
                'is_nullable' => ('YES' === $row['IS_NULLABLE']),
                'data_type' => $row['DATA_TYPE'],
                'character_maximum_length' => $row['CHARACTER_MAXIMUM_LENGTH'],
                'character_octet_length' => $row['CHARACTER_OCTET_LENGTH'],
                'numeric_precision' => $row['NUMERIC_PRECISION'],
                'numeric_scale' => $row['NUMERIC_SCALE'],
                'numeric_unsigned' => false,
                'comment' => '',
                'auto_increment' => (bool) $row['is_identity'],
                'erratas' => [],
            ];
        }

        return $columns;
    }

    /**
     * @inheritDoc
     */
    public function listConstraints(string $table, ?string $schema = null): array
    {
        $constraintGroup = $this->loadConstraintsStatement($table, $schema)
            ->all()
            ->mapProxy()
            ->apply(
                static function (array $storage) {
                    return array_change_key_case($storage, CASE_LOWER);
                }
            )
            ->group('constraint_name');

        $constraints = [];

        foreach ($constraintGroup as $name => $rows) {
            $constraints[$name] = [
                'constraint_name' => $name,
                'constraint_type' => $rows[0]['constraint_type'],
                'table_name' => $rows[0]['table_name'],
                'columns' => [],
            ];

            if ('CHECK' === $rows[0]['constraint_type']) {
                $constraints[$name]['check_clause'] = $rows[0]['check_clause'];
                continue;
            }

            $isFK = 'FOREIGN KEY' === $rows[0]['constraint_type'];

            if ($isFK) {
                $constraints[$name]['referenced_table_schema'] = $rows[0]['referenced_table_schema'];
                $constraints[$name]['referenced_table_name'] = $rows[0]['referenced_table_name'];
                $constraints[$name]['referenced_columns'] = [];
                $constraints[$name]['match_option'] = $rows[0]['match_option'];
                $constraints[$name]['update_rule'] = $rows[0]['update_rule'];
                $constraints[$name]['delete_rule'] = $rows[0]['delete_rule'];
            }

            foreach ($rows as $row) {
                if ('CHECK' === $row['constraint_type']) {
                    $constraints[$name]['check_clause'] = $row['check_clause'];
                    continue;
                }

                $constraints[$name]['columns'][] = $row['column_name'];

                if ($isFK) {
                    $constraints[$name]['referenced_columns'][] = $row['referenced_column_name'];
                }
            }
        }

        return $constraints;
    }

    /**
     * @inheritDoc
     */
    public function listIndexes(string $table, ?string $schema = null): array
    {
        $indexGroup = $this->loadIndexesStatement($table, $schema)
            ->all()
            ->group('index_name');

        $indexes = [];

        foreach ($indexGroup as $keys) {
            $index = [];
            $name = $keys[0]['index_name'];

            if ($keys[0]['is_primary_key']) {
                $name = 'PK__' . $keys[0]['table_name'];
            }

            if ($schema === null) {
                $name = $keys[0]['table_name'] . '_' . $name;
            }

            $index['table_schema'] = $keys[0]['schema_name'];
            $index['table_name'] = $keys[0]['table_name'];
            $index['is_unique'] = (bool) $keys[0]['is_unique'];
            $index['is_primary'] = (bool) ($keys[0]['is_primary_key'] ?: $keys[0]['is_identity']);
            $index['index_name'] = $keys[0]['index_name'];
            $index['index_comment'] = '';

            $index['columns'] = [];

            foreach ($keys as $key) {
                $index['columns'][$key['column_name']] = [
                    'column_name' => $key['column_name'],
                    'sub_part' => null,
                ];
            }

            $indexes[$name] = $index;
        }

        return $indexes;
    }

    /**
     * start
     *
     * @return  static
     */
    public function transactionStart(): static
    {
        if (!$this->depth) {
            parent::transactionStart();
        } else {
            $savepoint = 'SP_' . $this->depth;
            $this->db->execute('SAVE TRANSACTION ' . $this->db->quoteName($savepoint));

            $this->depth++;
        }

        return $this;
    }

    /**
     * commit
     *
     * @return  static
     */
    public function transactionCommit(): static
    {
        if ($this->depth <= 1) {
            parent::transactionCommit();
        }

        return $this;
    }

    /**
     * rollback
     *
     * @return  static
     */
    public function transactionRollback(): static
    {
        if ($this->depth <= 1) {
            parent::transactionRollback();
        } else {
            $savepoint = 'SP_' . ($this->depth - 1);
            $this->db->execute('ROLLBACK TRANSACTION ' . $this->db->quoteName($savepoint));

            $this->depth--;
        }

        return $this;
    }

    /**
     * getCurrentDatabase
     *
     * @return  string|null
     */
    public function getCurrentDatabase(): ?string
    {
        return $this->db->prepare('SELECT DB_NAME()')->result();
    }

    /**
     * dropDatabase
     *
     * @param  string  $name
     * @param  array   $options
     *
     * @return  StatementInterface
     */
    public function dropDatabase(string $name, array $options = []): StatementInterface
    {
        $query = $this->db->createQuery();
        $this->db->execute(
            $query->format('ALTER DATABASE %n SET SINGLE_USER WITH ROLLBACK IMMEDIATE', $name)
        );

        return parent::dropDatabase($name, $options);
    }

    /**
     * createTable
     *
     * @param  Schema  $schema
     * @param  bool    $ifNotExists
     * @param  array   $options
     *
     * @return  StatementInterface
     */
    public function createTable(Schema $schema, bool $ifNotExists = false, array $options = []): StatementInterface
    {
        $defaultOptions = [
            'auto_increment' => 1,
        ];

        $options = array_merge($defaultOptions, $options);
        $columns = [];
        $table = $schema->getTable();
        $tableName = $this->db->quoteName($table->schemaName . '.' . $table->getName());
        $comments = [];
        $primaries = [];

        foreach ($schema->getColumns() as $column) {
            $column = $this->prepareColumn(clone $column);

            if ($column->isPrimary()) {
                $primaries[] = $column;
            }

            $columns[$column->getColumnName()] = $this->getColumnExpression($column)
                ->setName($this->db->quoteName($column->getColumnName()));

            // Comment
            if ($column->getComment()) {
                $comments[$column->columnName] = $column->getComment();
            }
        }

        $sql = $this->getGrammar()::build(
            'CREATE TABLE',
            $tableName,
            "(\n" . implode(",\n", $columns) . "\n)",
            $this->getGrammar()::buildConfig([])
        );

        $statement = $this->db->execute($sql);

        if ($primaries) {
            $this->addConstraint(
                $table->getName(),
                (new Constraint(Constraint::TYPE_PRIMARY_KEY, 'pk_' . $table->getName(), $table->getName()))
                    ->columns($primaries),
                $table->schemaName
            );
        }

        foreach ($schema->getIndexes() as $index) {
            $this->addIndex($table->getName(), $index, $table->schemaName);
        }

        foreach ($schema->getConstraints() as $constraint) {
            $this->addConstraint($table->getName(), $constraint, $table->schemaName);
        }

        foreach ($comments as $column => $comment) {
            $this->addComment(
                'COLUMN',
                $table->getName(),
                $column,
                $comment
            );
        }

        return $statement;
    }

    public function renameTable(string $from, string $to, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'exec sp_rename',
                $this->db->quoteName($schema . '.' . $from),
                ', ',
                $this->db->quoteName($schema . '.' . $to),
            )
        );
    }

    /**
     * dropTable
     *
     * @param  string       $table
     * @param  string|null  $schema
     * @param  null         $suffix
     *
     * @return  StatementInterface
     */
    public function dropTable(string $table, ?string $schema = null, $suffix = null): StatementInterface
    {
        // Drop all foreign key reference to this table
        // @see https://social.msdn.microsoft.com/Forums/sqlserver/en-US/219f8a19-0026-49a1-a086-11c5d57d9c97/tsql-to-drop-all-constraints?forum=transactsql
        $dropFK = <<<SQL
DECLARE @str VARCHAR(MAX)
DECLARE cur CURSOR FOR

    SELECT 'ALTER TABLE ' + '[' + s.name + '].[' + t.name + '] DROP CONSTRAINT ['+ f.name + ']'
    FROM sys.foreign_keys AS f
    LEFT JOIN sys.objects AS t ON f.parent_object_id = t.object_id
    LEFT JOIN sys.schemas AS s ON t.schema_id = s.schema_id
    WHERE s.name = %q AND f.referenced_object_id = object_id(%q)
    ORDER BY t.type

OPEN cur
FETCH NEXT FROM cur INTO @str
WHILE (@@fetch_status = 0) BEGIN
    PRINT @str
    EXEC (@str)
    FETCH NEXT FROM cur INTO @str
END

CLOSE cur
DEALLOCATE cur;
SQL;

        return $this->db->execute(
            $this->getGrammar()::build(
                $this->createQuery()->format(
                    $dropFK,
                    $schema,
                    $table
                ),
                'DROP TABLE',
                'IF EXISTS',
                $this->db->quoteName($schema . '.' . $table),
                $suffix
            )
        );
    }

    public function getColumnExpression(Column $column): Clause
    {
        return $this->getGrammar()::build(
            $column->getTypeExpression(),
            $column->getIsNullable() ? '' : 'NOT NULL',
            $column->isAutoIncrement() ? 'IDENTITY' : null,
            $column->canHasDefaultValue()
                ? 'DEFAULT ' . $this->db->quote($column->getColumnDefault())
                : '',
            $column->getOption('suffix')
        );
    }

    public function prepareColumn(Column $column): Column
    {
        $type = $column->getDataType();
        $types = [
            'text',
            'json',
        ];

        if (in_array($type, $types, true)) {
            $column->characterMaximumLength('max');
        }

        $column = parent::prepareColumn($column);

        return $column;
    }

    public function addComment(string $type, string $table, string $name, string $comment): StatementInterface
    {
        $query = $this->db->createQuery();
        $table = $this->db->replacePrefix($table);

        return $this->db->execute(
            $this->getGrammar()::build(
                'exec sp_addextendedproperty',
                ...$query->quote(
                [
                    'MS_Description',
                    $comment,
                    'SCHEMA',
                    'dbo',
                    'TABLE',
                    $table,
                    $type,
                    $name,
                ]
            )
            )
        );
    }

    /**
     * addColumn
     *
     * @param  string       $table
     * @param  Column       $column
     * @param  string|null  $schema
     *
     * @return  StatementInterface
     */
    public function addColumn(string $table, Column $column, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'ADD',
                $this->db->quoteName($column->getColumnName()),
                (string) $this->getColumnExpression($column)
            )
        );
    }

    /**
     * modifyColumn
     *
     * @param  string       $table
     * @param  Column       $column
     * @param  string|null  $schema
     *
     * @return  StatementInterface
     */
    public function modifyColumn(string $table, Column $column, ?string $schema = null): StatementInterface
    {
        $this->dropColumnConstraints($table, $column->getColumnName(), $schema);

        $query = $this->createQuery();
        $sql = [];

        $sql[] = $this->getGrammar()::build(
            'ALTER TABLE',
            $query->qn($schema . '.' . $table),
            'ALTER COLUMN',
            $this->db->quoteName($column->getColumnName()),
            $column->getTypeExpression(),
            $column->getIsNullable() ? 'NOT NULL' : null,
        );

        if ($column->getColumnDefault() !== false) {
            $sql[] = $this->getGrammar()::build(
                'ALTER TABLE',
                $query->qn($schema . '.' . $table),
                'ADD DEFAULT',
                $this->db->quote($column->getColumnDefault()),
                'FOR',
                $this->db->quoteName($column->getColumnName())
            );
        }

        $stmt = $this->db->execute(implode(';', $sql));

        if ($column->getComment()) {
            $this->addComment(
                'COLUMN',
                $this->db->replacePrefix($table),
                $column->getColumnName(),
                $column->getColumnName()
            );
        }

        return $stmt;
    }

    public function dropColumnConstraints(string $table, string $column, ?string $schema = null): void
    {
        $query = $this->db->createQuery();

        // Sqlsrv must drop default constraint first
        // todo: join sys.schemas
        $q = <<<SQL
        DECLARE @ConstraintName nvarchar(200)

        SELECT @ConstraintName = Name FROM SYS.DEFAULT_CONSTRAINTS
        WHERE PARENT_OBJECT_ID = OBJECT_ID(%q)
        AND PARENT_COLUMN_ID = (SELECT column_id FROM sys.columns
                                WHERE NAME = N%q
                                AND object_id = OBJECT_ID(N%q))

        IF @ConstraintName IS NOT NULL
        EXEC('ALTER TABLE %n DROP CONSTRAINT [' + @ConstraintName + ']')
        SQL;

        $table = $this->db->replacePrefix($table);

        $this->db->execute($query->format($q, $table, $column, $table, $table));

        $constraints = $this->listConstraints($table, $schema);

        foreach ($constraints as $key => $constraint) {
            if (in_array($column, $constraint['columns'], true)) {
                $this->dropConstraint($table, $constraint['constraint_name'], $schema);

                unset($constraints[$key]);
            }
        }

        $indexes = $this->listIndexes($table, $schema);

        foreach ($indexes as $key => $index) {
            if (array_key_exists($column, $index['columns'])) {
                $this->dropIndex($table, $index['index_name'], $schema);

                unset($indexes[$key]);
            }
        }
    }

    /**
     * renameColumn
     *
     * @param  string       $table
     * @param  string       $from
     * @param  string       $to
     * @param  string|null  $schema
     *
     * @return  StatementInterface
     */
    public function renameColumn(string $table, string $from, string $to, ?string $schema = null): StatementInterface
    {
        throw new LogicException('Currently not support rename column');
    }

    /**
     * dropIndex
     *
     * @param  string       $table
     * @param  string       $name
     * @param  string|null  $schema
     *
     * @return  StatementInterface
     */
    public function dropIndex(string $table, string $name, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'DROP INDEX',
                $this->db->quoteName($schema . '.' . $table . '.' . $name)
            )
        );
    }
}
