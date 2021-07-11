<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Platform;

use PDO;
use PDOException;
use Windwalker\Data\Collection;
use Windwalker\Database\Driver\Pdo\PdoDriver;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Platform\Type\PostgreSQLDataType;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Schema;
use Windwalker\Query\Clause\JoinClause;
use Windwalker\Query\Escaper;
use Windwalker\Query\Query;
use Windwalker\Scalars\ArrayObject;

use function Windwalker\Query\clause;
use function Windwalker\raw;

/**
 * The PostgresqlPlatform class.
 */
class PostgreSQLPlatform extends AbstractPlatform
{
    protected string $name = self::POSTGRESQL;

    protected static ?string $defaultSchema = 'public';

    public function listDatabasesQuery(): Query
    {
        return $this->createQuery()
            ->select('datname')
            ->from('pg_database')
            ->where('datistemplate', raw('false'));
    }

    public function listSchemaQuery(): Query
    {
        return $this->db->createQuery()
            ->select('schema_name')
            ->from('information_schema.schemata')
            ->whereNotIn('schema_name', ['pg_catalog', 'information_schema']);
    }

    public function listTablesQuery(?string $schema): Query
    {
        $query = $this->createQuery()
            ->select('table_name AS TABLE_NAME')
            ->select('table_catalog AS TABLE_CATALOG')
            ->select('table_schema AS TABLE_SCHEMA')
            ->select('table_type AS TABLE_TYPE')
            ->selectAs(null, 'VIEW_DEFINITION', false)
            ->selectAs(null, 'CHECK_OPTION', false)
            ->selectAs(null, 'IS_UPDATABLE', false)
            ->from('information_schema.tables')
            ->where('table_type', 'BASE TABLE')
            ->order('table_name', 'ASC');

        if ($schema) {
            $query->where('table_schema', $schema);
        } else {
            $query->whereNotIn('table_schema', ['pg_catalog', 'information_schema']);
        }

        return $query;
    }

    public function listViewsQuery(?string $schema): Query
    {
        $query = $this->createQuery()
            ->select('table_name AS TABLE_NAME')
            ->select('table_catalog AS TABLE_CATALOG')
            ->select('table_schema AS TABLE_SCHEMA')
            ->selectAs('VIEW', 'TABLE_TYPE', false)
            ->selectAs('view_definition', 'VIEW_DEFINITION')
            ->selectAs('check_option', 'CHECK_OPTION')
            ->selectAs('is_updatable', 'IS_UPDATABLE')
            ->from('information_schema.views')
            ->order('table_name', 'ASC');

        if ($schema) {
            $query->where('table_schema', $schema);
        } else {
            $query->whereNotIn('table_schema', ['pg_catalog', 'information_schema']);
        }

        return $query;
    }

    public function listColumnsQuery(string $table, ?string $schema): Query
    {
        $query = $this->db->createQuery()
            ->select(
                [
                    'ordinal_position',
                    'column_default',
                    'is_nullable',
                    'data_type',
                    'character_maximum_length',
                    'character_octet_length',
                    'numeric_precision',
                    'numeric_scale',
                    'column_name',
                ]
            )
            ->from('information_schema.columns')
            ->where('table_name', $this->db->replacePrefix($table));

        if ($schema !== null) {
            $query->where('table_schema', $schema);
        } else {
            $query->whereNotIn('table_schema', ['pg_catalog', 'information_schema']);
        }

        return $query;
    }

    public function listConstraintsQuery(string $table, ?string $schema): Query
    {
        return $this->createQuery()->select(
            [
                't.table_name',
                'tc.constraint_name',
                'tc.constraint_type',
                'kcu.column_name',
                'cc.check_clause',
                'rc.match_option',
                'rc.update_rule',
                'rc.delete_rule',
                'kcu2.table_schema AS referenced_table_schema',
                'kcu2.table_name AS referenced_table_name',
                'kcu2.column_name AS referenced_column_name',
            ]
        )
            ->from('information_schema.tables', 't')
            ->innerJoin(
                'information_schema.table_constraints',
                'tc',
                [
                    ['t.table_schema', '=', 'tc.table_schema'],
                    ['t.table_name', '=', 'tc.table_name'],
                ]
            )
            ->leftJoin(
                'information_schema.key_column_usage',
                'kcu',
                [
                    ['kcu.table_schema', '=', 'tc.table_schema'],
                    ['kcu.table_name', '=', 'tc.table_name'],
                    ['kcu.constraint_name', '=', 'tc.constraint_name'],
                ]
            )
            ->leftJoin(
                'information_schema.check_constraints',
                'cc',
                [
                    ['cc.constraint_schema', '=', 'tc.constraint_schema'],
                    ['cc.constraint_name', '=', 'tc.constraint_name'],
                ]
            )
            ->leftJoin(
                'information_schema.referential_constraints',
                'rc',
                [
                    ['rc.constraint_schema', '=', 'tc.constraint_schema'],
                    ['rc.constraint_name', '=', 'tc.constraint_name'],
                ]
            )
            ->leftJoin(
                'information_schema.key_column_usage',
                'kcu2',
                [
                    ['rc.unique_constraint_schema', '=', 'kcu2.constraint_schema'],
                    ['rc.unique_constraint_name', '=', 'kcu2.constraint_name'],
                    ['kcu.position_in_unique_constraint', '=', 'kcu2.ordinal_position'],
                ]
            )
            ->where('t.table_name', $this->db->replacePrefix($table))
            ->where('t.table_type', 'in', ['BASE TABLE', 'VIEW'])
            ->tap(
                function (Query $query) use ($schema) {
                    if ($schema !== null) {
                        $query->where('t.table_schema', $schema);
                    } else {
                        $query->whereNotIn('t.table_schema', ['pg_catalog', 'information_schema']);
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
                            'tc.constraint_type',
                            'tc.constraint_name',
                            'kcu.ordinal_position'
                        )
                    );
                }
            );
    }

    public function listIndexesQuery(string $table, ?string $schema): Query
    {
        $query = $this->createQuery();

        $query->select('ix.*')
            ->selectAs(raw('tc.constraint_type = \'PRIMARY KEY\''), 'is_primary')
            ->from('pg_indexes', 'ix')
            ->leftJoin(
                'information_schema.table_constraints',
                'tc',
                static function (JoinClause $join) {
                    $join->on('tc.table_schema', 'ix.schemaname');
                    $join->on('tc.constraint_name', 'ix.indexname');
                    $join->onRaw('tc.constraint_type = %q', 'PRIMARY KEY');
                }
            )
            ->where('tablename', $this->db->replacePrefix($table));

        $order = 'CASE tc.constraint_type WHEN \'PRIMARY KEY\' THEN 1 ELSE 2 END';

        $query->order(raw($order));

        if ($schema !== null) {
            $query->where('schemaname', $schema);
        } else {
            $query->whereNotIn('schemaname', ['pg_catalog', 'information_schema']);
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function listColumns(string $table, ?string $schema = null): array
    {
        $columns = [];

        foreach ($this->loadColumnsStatement($table, $schema) as $row) {
            $columns[$row['column_name']] = [
                'column_name' => $row['column_name'],
                'ordinal_position' => $row['ordinal_position'],
                'column_default' => $row['column_default'],
                'is_nullable' => ('YES' === $row['is_nullable']),
                'data_type' => $row['data_type'],
                'character_maximum_length' => $row['character_maximum_length'],
                'character_octet_length' => $row['character_octet_length'],
                'numeric_precision' => $row['numeric_precision'],
                'numeric_scale' => $row['numeric_scale'],
                'numeric_unsigned' => false,
                'comment' => '',
                'auto_increment' => false,
                'erratas' => [],
            ];
        }

        foreach ($columns as &$column) {
            if (str_contains((string) $column['column_default'], 'nextval')) {
                $column['auto_increment'] = true;
                $column['column_default'] = 0;
            }

            if (preg_match('/^NULL::*/', (string) $column['column_default'])) {
                $column['column_default'] = null;
            }

            if (preg_match("/'(.*)'::[\w\s]/", (string) $column['column_default'], $matches)) {
                $column['column_default'] = $matches[1] ?? '';
            }

            if (str_contains((string) $column['data_type'], 'character varying')) {
                $column['data_type'] = str_replace('character varying', 'varchar', $column['data_type']);
            }
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
            ->group('constraint_name');

        $name = null;
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
        $indexes = [];

        foreach ($this->loadIndexesStatement($table, $schema) as $row) {
            preg_match(
                '/CREATE (UNIQUE )?INDEX [\w]+ ON [\w.]+ USING [\w]+ \(([\w, ]+)\)/',
                $row['indexdef'],
                $matches
            );

            $index['table_schema'] = $row['schemaname'];
            $index['table_name'] = $row['tablename'];
            $index['is_unique'] = trim($matches[1]) === 'UNIQUE';
            $index['is_primary'] = (bool) $row['is_primary'];
            $index['index_name'] = $row['indexname'];
            $index['index_comment'] = '';

            $index['columns'] = [];

            $columns = ArrayObject::explode(',', $matches[2])
                ->map('trim')
                ->map(
                    static function (string $index) {
                        return Escaper::stripQuoteIfExists($index, '"');
                    }
                )
                ->dump();

            foreach ($columns as $column) {
                $index['columns'][$column] = [
                    'column_name' => $column,
                    'sub_part' => null,
                ];
            }

            $indexes[$row['indexname']] = $index;
        }

        return $indexes;
    }

    public function lastInsertId($insertQuery, ?string $sequence = null): ?string
    {
        if ($sequence && $this->db->getDriver() instanceof PdoDriver) {
            /** @var PDO $pdo */
            $pdo = $this->db->getDriver()->getConnectionFromPool()->get();

            return $pdo->lastInsertId($sequence);
        }

        if ($insertQuery instanceof Query) {
            $table = $insertQuery->getInsert()->getElements();
        } else {
            preg_match('/insert\s*into\s*[\"]*(\W\w+)[\"]*/i', $insertQuery, $matches);

            if (!isset($matches[1])) {
                return null;
            }

            $table = [$matches[1]];
        }

        /* find sequence column name */
        $colNameQuery = $this->createQuery();

        $colNameQuery->select('column_default')
            ->from('information_schema.columns')
            ->where('table_name', $this->db->replacePrefix(trim($table[0], '" ')))
            ->where('column_default', 'LIKE', '%nextval%');

        $colName = $this->db->prepare($colNameQuery)->get()->first();

        $changedColName = str_replace('nextval', 'currval', $colName);

        $insertidQuery = $this->createQuery();

        $insertidQuery->selectRaw($changedColName);

        try {
            return $this->db->prepare($insertidQuery)->result();
        } catch (PDOException $e) {
            // 55000 means we trying to insert value to serial column
            // Just return because insertedId get the last generated value.
            if ($e->getCode() !== 55000) {
                throw $e;
            }
        }

        return null;
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
            $this->db->execute('SAVEPOINT ' . $this->db->quoteName($savepoint));

            $this->depth++;
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
            $this->db->execute('ROLLBACK TO SAVEPOINT ' . $this->db->quoteName($savepoint));

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
        return $this->db->prepare('SELECT current_database()')->result();
    }

    public function dropDatabase(string $name, array $options = []): StatementInterface
    {
        // $pid = version_compare($this->db->getVersion(), '9.2', '>=') ? 'pid' : 'procpid';
        // $query = $this->db->createQuery();
        // $query->select('pg_terminate_backend(' . $pid . ')')
        //     ->from('pg_stat_activity')
        //     ->where('datname = ' . $query->quote($this->getName()));
        //
        // $this->db->setQuery($query)->execute();

        return $this->db->execute(
            $this->getGrammar()
                ::build(
                    'DROP DATABASE',
                    $this->db->quoteName($name)
                )
        );
    }

    /**
     * dropSchema
     *
     * @param  string  $name
     *
     * @param  array   $options
     *
     * @return StatementInterface
     */
    public function dropSchema(string $name, array $options = []): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()
                ::build(
                    'DROP SCHEMA',
                    $this->db->quoteName($name),
                    'CASCADE'
                )
        );
    }

    /**
     * createTable
     *
     * @param  Schema  $schema
     * @param  bool    $ifNotExists
     * @param  array   $options
     *
     * @return  bool
     */
    public function createTable(Schema $schema, bool $ifNotExists = false, array $options = []): StatementInterface
    {
        $defaultOptions = [
            'auto_increment' => 1,
            'sequences' => [],
            'inherits' => [],
            'tablespace' => null,
        ];

        $options = array_merge($defaultOptions, $options);
        $columns = [];
        $table = $schema->getTable();
        $tableName = $this->db->quoteName($table->schemaName . '.' . $table->getName());
        $primaries = [];
        $comments = [];

        foreach ($schema->getColumns() as $column) {
            $column = $this->prepareColumn(clone $column);

            if ($column->isPrimary()) {
                $column->dataType(PostgreSQLDataType::SERIAL);

                $primaries[] = $column;

                // Add AI later after table created.
                $column = clone $column;
                $column->autoIncrement(false);
            }

            // Comment
            if ($column->getComment()) {
                $comments[$column->getColumnName()] = $column->getComment();
            }

            $columns[$column->getColumnName()] = $this->getColumnExpression($column)
                ->setName($this->db->quoteName($column->getColumnName()));
        }

        $sql = $this->getGrammar()::build(
            'CREATE TABLE',
            $ifNotExists ? 'IF NOT EXISTS' : null,
            $tableName,
            "(\n" . implode(",\n", $columns) . "\n)",
            $this->getGrammar()::buildConfig(
                [
                    'INHERITS' => $options['inherits']
                        ? (string) clause('()', $this->db->quoteName($options['inherits']))
                        : null,
                    'TABLESPACE' => $options['tablespace'] ? $this->db->quoteName($options['tablespace']) : null,
                ],
                ' '
            )
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

        foreach ($comments as $comment) {
            $this->db->execute(
                $this->getGrammar()::build(
                    'COMMENT ON',
                    'COLUMN',
                    $tableName,
                    'IS',
                    $this->db->quote($comment)
                )
            );
        }

        return $statement;
    }

    public function modifyColumn(string $table, Column $column, ?string $schema = null): StatementInterface
    {
        $alter = $this->createQuery()
            ->alter('TABLE', $schema . '.' . $table);

        $alter->subClause('ALTER COLUMN')
            ->append(
                [
                    $this->db->quoteName($column->getColumnName()),
                    'TYPE',
                    $column->getTypeExpression(),
                ]
            );

        $alter->subClause('ALTER COLUMN')
            ->append(
                [
                    $this->db->quoteName($column->getColumnName()),
                    $column->getIsNullable() ? 'SET' : 'DROP',
                    'NOT NULL',
                ]
            );

        if ($column->getColumnDefault() !== false) {
            $alter->subClause('ALTER COLUMN')
                ->append(
                    [
                        $this->db->quoteName($column->getColumnName()),
                        'SET DEFAULT',
                        $this->db->quote($column->getColumnDefault()),
                    ]
                );
        }

        $sql = [(string) $alter];

        if ($column->getComment()) {
            $sql[] = $this->getGrammar()::build(
                'COMMENT ON',
                'COLUMN',
                $this->db->quoteName($schema . '.' . $table),
                'IS',
                $this->db->quote($column->getComment())
            );
        }

        return $this->db->execute(implode(";", $sql));
    }

    /**
     * renameColumn
     *
     * @param  string       $table
     * @param  string       $from
     * @param  string       $to
     *
     * @param  string|null  $schema
     *
     * @return StatementInterface
     */
    public function renameColumn(string $table, string $from, string $to, ?string $schema = null): StatementInterface
    {
        $alter = $this->createQuery()
            ->alter('TABLE', $schema . '.' . $table);

        $alter->subClause('RENAME COLUMN')
            ->append(
                [
                    $this->db->quoteName($from),
                    'TO',
                    $this->db->quoteName($to),
                ]
            );

        return $this->db->execute((string) $alter);
    }

    public function getTableSequences(string $table, ?string $schema = null): ?Collection
    {
        // To check if table exists and prevent SQL injection
        $tableList = $this->listTables($schema);

        $table = $this->db->replacePrefix($table);

        if (array_key_exists($table, $tableList)) {
            $name = [
                's.relname AS sequence',
                'n.nspname AS schema',
                't.relname AS table',
                'a.attname AS column',
                'info.data_type AS data_type',
                'info.minimum_value AS minimum_value',
                'info.maximum_value AS maximum_value',
                'info.increment AS increment',
                'info.cycle_option AS cycle_option',
            ];

            if (version_compare($this->db->getDriver()->getVersion(), '9.1.0', '>=')) {
                $name[] .= 'info.start_value AS start_value';
            }

            // Get the details columns information.
            $query = $this->db->createQuery();

            $query->select($name)
                ->from('pg_class AS s')
                ->leftJoin(
                    'pg_depend',
                    'd',
                    static function (JoinClause $join) {
                        $join->on("d.objid", '=', "s.oid");
                        $join->onRaw("d.classid = 'pg_class'::regclass");
                        $join->onRaw("d.refclassid = 'pg_class'::regclass");
                    }
                )
                ->leftJoin('pg_class', 't', 't.oid', 'd.refobjid')
                ->leftJoin('pg_namespace', 'n', 'n.oid', 't.relnamespace')
                ->leftJoin(
                    'pg_attribute',
                    'a',
                    function (JoinClause $join) {
                        $join->on('a.attrelid', 't.oid');
                        $join->on('a.attnum', 'd.refobjsubid');
                    }
                )
                ->leftJoin('information_schema.sequences', 'info', 'info.sequence_name', 's.relname')
                ->where('s.relkind', 'S')
                ->where('d.deptype', 'a')
                ->where('t.relname', $table);

            return $this->db->prepare($query)->all();
        }

        return null;
    }
}
