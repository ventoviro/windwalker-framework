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
use Windwalker\Database\Platform\Type\DataType;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Schema;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Escaper;
use Windwalker\Query\Query;
use Windwalker\Utilities\TypeCast;

use function Windwalker\raw;

/**
 * The SqlitePlatform class.
 */
class SQLitePlatform extends AbstractPlatform
{
    protected string $name = self::SQLITE;

    protected static ?string $defaultSchema = 'main';

    public function listDatabasesQuery(): Query
    {
        return $this->pragma('database_list');
    }

    public function listSchemaQuery(): Query
    {
        return $this->listDatabasesQuery();
    }

    public function listTablesQuery(?string $schema): Query
    {
        $schema = $schema ?? static::getDefaultSchema();

        return $this->createQuery()
            ->select(
                [
                    'name AS TABLE_NAME',
                    raw('\'BASE TABLE\' AS TABLE_TYPE'),
                    raw(sprintf('\'%s\' AS TABLE_SCHEMA', $schema)),
                    raw('NULL AS VIEW_DEFINITION'),
                    raw('NULL AS CHECK_OPTION'),
                    raw('NULL AS IS_UPDATABLE'),
                    'sql',
                ]
            )
            ->from($schema . '.sqlite_master')
            ->where('type', 'table')
            ->where('name', 'not like', 'sqlite_%')
            ->order('name');
    }

    public function listViewsQuery(?string $schema): Query
    {
        return $this->createQuery()
            ->select(
                [
                    'name AS TABLE_NAME',
                    raw('\'VIEW\' AS TABLE_TYPE'),
                    raw(sprintf('\'%s\' AS TABLE_SCHEMA', $schema)),
                    raw('NULL AS VIEW_DEFINITION'),
                    raw('\'NONE\' AS CHECK_OPTION'),
                    raw('NULL AS IS_UPDATABLE'),
                    'sql',
                ]
            )
            ->from(trim($schema . '.sqlite_master', '.'))
            ->where('type', 'view')
            ->where('name', 'not like', 'sqlite_%')
            ->order('name');
    }

    /**
     * @inheritDoc
     */
    public function listSchemas(): array
    {
        return $this->db->prepare(
            $this->listSchemaQuery()
        )
            ->loadColumn(1)
            ->dump();
    }

    public function listViews(?string $schema = null): array
    {
        $views = parent::listViews($schema);

        foreach ($views as $view) {
            preg_match('/create\s+view\s+.+AS\s([.\w\W]+)/mi', $view['sql'], $matches);

            $view['VIEW_DEFINITION'] = $matches[1] ?? null;
        }

        return $views;
    }

    public function listColumnsQuery(string $table, ?string $schema): Query
    {
        return $this->pragma('table_info', $this->db->replacePrefix($table), $schema);
    }

    public function listConstraintsQuery(string $table, ?string $schema): Query
    {
        return $this->listIndexesQuery($table, $schema);
    }

    public function listIndexesQuery(string $table, ?string $schema): Query
    {
        return $this->pragma('index_list', $this->db->replacePrefix($table), $schema);
    }

    /**
     * @inheritDoc
     */
    public function listDatabases(): array
    {
        return $this->db->prepare(
            $this->listDatabasesQuery()
        )
            ->loadColumn(2)
            ->dump();
    }

    /**
     * @inheritDoc
     */
    public function listColumns(string $table, ?string $schema = null): array
    {
        $columns = [];

        foreach ($this->loadColumnsStatement($table, $schema) as $row) {
            [$type, $precision, $scale] = DataType::extract($row['type']);

            $isString = in_array(
                $type = strtolower($type),
                [
                    'char',
                    'varchar',
                    'text',
                    'mediumtext',
                    'longtext',
                ]
            );

            $columns[$row['name']] = [
                'column_name' => $row['name'],
                // cid appears to be zero-based, ordinal position needs to be one-based
                'ordinal_position' => $row['cid'] + 1,
                'column_default' => Escaper::stripQuoteIfExists($row['dflt_value']),
                'is_nullable' => !$row['notnull'],
                'data_type' => $type,
                'character_maximum_length' => $isString ? TypeCast::tryInteger($precision, true) : null,
                'character_octet_length' => null,
                'numeric_precision' => $isString ? null : TypeCast::tryInteger($precision, true),
                'numeric_scale' => $isString ? null : TypeCast::tryInteger($scale, true),
                'numeric_unsigned' => false,
                'comment' => null,
                'auto_increment' => (bool) $row['pk'],
                'erratas' => [
                    'pk' => (bool) $row['pk'],
                ],
            ];
        }

        return $columns;
    }

    /**
     * @inheritDoc
     */
    public function listConstraints(string $table, ?string $schema = null): array
    {
        $constraints = [];

        $columns = $this->listColumns($table, $schema);

        $primaryKey = [];

        foreach ($columns as $name => $column) {
            if ($column['erratas']['pk']) {
                $primaryKey[] = $name;
            }
        }

        foreach ($this->loadConstraintsStatement($table, $schema) as $row) {
            if (!$row['unique']) {
                continue;
            }

            $constraint = [
                'constraint_name' => $row['name'],
                'constraint_type' => 'UNIQUE',
                'table_name' => $this->db->replacePrefix($table),
                'columns' => [],
            ];

            $info = $this->db->prepare(
                $this->pragma('index_info', $row['name'], $schema)
            );

            foreach ($info as $column) {
                $constraint['columns'][] = $column['name'];
            }

            if ($primaryKey === $constraint['columns']) {
                $constraint['constraint_type'] = 'PRIMARY KEY';
                $primaryKey = null;
            }

            $constraints[$constraint['constraint_name']] = $constraint;
        }

        return $constraints;
    }

    /**
     * @inheritDoc
     */
    public function listIndexes(string $table, ?string $schema = null): array
    {
        $indexes = [];

        $columns = $this->listColumns($table, $schema);

        $primaryKey = [];

        foreach ($columns as $name => $column) {
            if ($column['erratas']['pk']) {
                $primaryKey[] = $name;
            }
        }

        foreach ($this->loadConstraintsStatement($table, $schema) as $row) {
            $index['table_schema'] = $schema;
            $index['table_name'] = $this->db->replacePrefix($table);
            $index['is_unique'] = (bool) $row['unique'];
            $index['index_name'] = $row['name'];
            $index['index_comment'] = '';

            $index['columns'] = [];

            $info = $this->db->prepare(
                $this->pragma('index_info', $row['name'], $schema)
            );

            foreach ($info as $column) {
                $index['columns'][$column['name']] = [
                    'column_name' => $column['name'],
                    'subpart' => null,
                ];
            }

            if ($primaryKey === $index['columns']) {
                $index['is_primary'] = true;
                $primaryKey = null;
            }

            $indexes[$row['name']] = $index;
        }

        return $indexes;
    }

    public function pragma(string $name, ?string $value = null, ?string $schema = null): Query
    {
        $query = $this->createQuery();

        $sql = 'PRAGMA ';

        if (null !== $schema) {
            $sql .= $query->quoteName($schema) . '.';
        }

        $sql .= $name;

        if (null !== $value) {
            $sql .= '(' . $query->quote($value) . ')';
        }

        return $query->sql($sql);
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
     * commit
     *
     * @return  static
     */
    public function transactionCommit(): static
    {
        if ($this->depth <= 1) {
            parent::transactionCommit();
        } else {
            $this->depth--;
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

    public function createDatabase(string $name, array $options = []): StatementInterface
    {
        $as = $options['as'] ?? pathinfo($name, PATHINFO_BASENAME);

        return $this->db->execute(
            $this->getGrammar()
                ::build(
                    'ATTACH DATABASE',
                    $this->db->quote($name),
                    'AS',
                    $this->db->quoteName($as)
                )
        );
    }

    public function createSchema(string $name, array $options = []): StatementInterface
    {
        return $this->createDatabase($name, $options);
    }

    /**
     * getCurrentDatabase
     *
     * @return  string|null
     */
    public function getCurrentDatabase(): ?string
    {
        $databases = $this->db->prepare($this->pragma('database_list'))
            ->all()
            ->keyBy('name');

        return $databases[static::getDefaultSchema()]->file ?? null;
    }

    public function dropDatabase(string $name, array $options = []): StatementInterface
    {
        $databases = $this->db->prepare($this->pragma('database_list'))
            ->all()
            ->keyBy('file');

        if ($databases[$name]) {
            $dbname = $databases[$name]->name;

            return $this->db->execute(
                $this->getGrammar()
                    ::build(
                        'DETACH DATABASE',
                        $this->db->quoteName($dbname)
                    )
            );
        }

        return $this->dropSchema($name, $options);
    }

    /**
     * dropSchema
     *
     * @param  string  $name
     *
     * @param  array   $options
     *
     * @return  StatementInterface
     */
    public function dropSchema(string $name, array $options = []): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()
                ::build(
                    'DETACH DATABASE',
                    $this->db->quoteName($name)
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
     * @return  StatementInterface
     */
    public function createTable(Schema $schema, bool $ifNotExists = false, array $options = []): StatementInterface
    {
        $defaultOptions = [];

        $options = array_merge($defaultOptions, $options);
        $columns = [];
        $table = $schema->getTable();
        $tableName = $this->db->quoteName($table->schemaName . '.' . $table->getName());
        $primaries = [];

        foreach ($schema->getColumns() as $column) {
            $column = $this->prepareColumn(clone $column);

            if ($column->isPrimary()) {
                $primaries[] = $column;
            }

            $columns[$column->getColumnName()] = $this->getColumnExpression($column)
                ->setName($this->db->quoteName($column->getColumnName()));
        }

        $alter = $this->createQuery()->alter('', '');
        $alter->getClause()->setName('');

        $constraints = $schema->getConstraints();

        if ($primaries) {
            $constraints[] = (new Constraint(
                Constraint::TYPE_PRIMARY_KEY,
                'pk_' . $table->getName(),
                $table->getName()
            ))
                ->columns($primaries);
        }

        foreach ($constraints as $constraint) {
            $columns[] = $alter->addConstraint(
                $constraint->constraintName,
                $constraint->constraintType,
                $this->prepareKeyColumns($constraint->getColumns())
            )
                ->setName('CONSTRAINT');
        }

        $sql = $this->getGrammar()::build(
            'CREATE TABLE',
            $ifNotExists ? 'IF NOT EXISTS' : null,
            $tableName,
            "(\n" . implode(",\n", $columns) . "\n)",
            $this->getGrammar()::buildConfig([])
        );

        $statement = $this->db->execute($sql);

        foreach ($schema->getIndexes() as $index) {
            $this->addIndex($table->getName(), $index, $table->schemaName);
        }

        return $statement;
    }

    protected function getKeyColumnExpression(Column $column): Clause
    {
        $expr = parent::getKeyColumnExpression($column);

        if ($column->isAutoIncrement()) {
            $expr->append('AUTOINCREMENT');
        }

        return $expr;
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
        throw new LogicException('Current SQLitePlatform not support: ' . __FUNCTION__ . '()');
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
        throw new LogicException('Current SQLitePlatform not support: ' . __FUNCTION__ . '()');
    }

    /**
     * addConstraint
     *
     * @param  string       $table
     * @param  Constraint   $constraint
     * @param  string|null  $schema
     *
     * @return  StatementInterface
     */
    public function addConstraint(string $table, Constraint $constraint, ?string $schema = null): StatementInterface
    {
        throw new LogicException('Current SQLitePlatform not support: ' . __FUNCTION__ . '()');
    }

    /**
     * dropColumn
     *
     * @param  string       $table
     * @param  string       $name
     * @param  string|null  $schema
     *
     * @return  StatementInterface
     */
    public function dropColumn(string $table, string $name, ?string $schema = null): StatementInterface
    {
        throw new LogicException('Current SQLitePlatform not support: ' . __FUNCTION__ . '()');
    }

    /**
     * truncateTable
     *
     * @param  string       $table
     * @param  string|null  $schema
     *
     * @return  StatementInterface
     */
    public function truncateTable(string $table, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'DELETE FROM',
                $this->db->quoteName($schema . '.' . $table)
            )
        );
    }
}
