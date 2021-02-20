<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Platform;

use Windwalker\Data\Collection;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Ddl\Index;
use Windwalker\Database\Schema\Schema;
use Windwalker\Query\Clause\AlterClause;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Escaper;
use Windwalker\Query\Mysql\MysqlGrammar;
use Windwalker\Query\Query;
use Windwalker\Utilities\Str;

use function Windwalker\raw;

/**
 * The MysqlPlatform class.
 */
class MySQLPlatform extends AbstractPlatform
{
    protected string $name = self::MYSQL;

    /**
     * @inheritDoc
     */
    public function listDatabasesQuery(): Query
    {
        return $this->listSchemaQuery();
    }

    public function listSchemaQuery(): Query
    {
        return $this->createQuery()
            ->select('SCHEMA_NAME')
            ->from('INFORMATION_SCHEMA.SCHEMATA')
            ->where('SCHEMA_NAME', '!=', 'INFORMATION_SCHEMA');
    }

    public function listTablesQuery(?string $schema): Query
    {
        $query = $this->createQuery()
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
            ->where('TABLE_TYPE', 'BASE TABLE');

        if ($schema !== null) {
            $query->where('TABLE_SCHEMA', $schema);
        } else {
            $query->where('TABLE_SCHEMA', raw('(SELECT DATABASE())'));
        }

        return $query;
    }

    public function listViewsQuery(?string $schema): Query
    {
        $query = $this->db->select(
            [
                'TABLE_NAME',
                'TABLE_SCHEMA',
                raw('\'VIEW\' AS TABLE_TYPE'),
                'VIEW_DEFINITION',
                'CHECK_OPTION',
                'IS_UPDATABLE',
            ]
        )
            ->from('INFORMATION_SCHEMA.VIEWS');

        if ($schema !== null) {
            $query->where('TABLE_SCHEMA', $schema);
        } else {
            $query->where('TABLE_SCHEMA', raw('(SELECT DATABASE())'));
        }

        return $query;
    }

    public function listColumnsQuery(string $table, ?string $schema): Query
    {
        $query = $this->db->select(
            [
                'ORDINAL_POSITION',
                'COLUMN_DEFAULT',
                'IS_NULLABLE',
                'DATA_TYPE',
                'CHARACTER_MAXIMUM_LENGTH',
                'CHARACTER_OCTET_LENGTH',
                'NUMERIC_PRECISION',
                'NUMERIC_SCALE',
                'COLUMN_NAME',
                'COLUMN_TYPE',
                'COLUMN_COMMENT',
                'EXTRA',
            ]
        )
            ->from('INFORMATION_SCHEMA.COLUMNS')
            ->where('TABLE_NAME', $this->db->replacePrefix($table));

        if ($schema !== null) {
            $query->where('TABLE_SCHEMA', $schema);
        } else {
            $query->where('TABLE_SCHEMA', raw('(SELECT DATABASE())'));
        }

        return $query;
    }

    public function listIndexesQuery(string $table, ?string $schema): Query
    {
        $query = $this->db->select(
            [
                'TABLE_SCHEMA',
                'TABLE_NAME',
                'NON_UNIQUE',
                'INDEX_NAME',
                'COLUMN_NAME',
                'COLLATION',
                'CARDINALITY',
                'SUB_PART',
                'INDEX_COMMENT',
            ]
        )
            ->from('INFORMATION_SCHEMA.STATISTICS')
            ->where('TABLE_NAME', $this->db->replacePrefix($table));

        if ($schema !== null) {
            $query->where('TABLE_SCHEMA', $schema);
        } else {
            $query->where('TABLE_SCHEMA', raw('(SELECT DATABASE())'));
        }

        return $query;
    }

    public function listConstraintsQuery(string $table, ?string $schema): Query
    {
        return $this->db->select(
            [
                'TABLE_NAME',
                'CONSTRAINT_NAME',
                'CONSTRAINT_TYPE',
            ]
        )
            ->from('INFORMATION_SCHEMA.TABLE_CONSTRAINTS')
            ->where('TABLE_NAME', $this->db->replacePrefix($table))
            ->tap(
                static function (Query $query) use ($schema) {
                    if ($schema !== null) {
                        $query->where('TABLE_SCHEMA', $schema);
                    } else {
                        $query->where('TABLE_SCHEMA', raw('(SELECT DATABASE())'));
                    }
                }
            );
    }

    /**
     * @inheritDoc
     */
    public function listColumns(string $table, ?string $schema = null): array
    {
        $columns = [];

        foreach ($this->loadColumnsStatement($table, $schema) as $row) {
            $erratas = [];
            $matches = [];

            // Enum/Set
            if (preg_match('/^(?:enum|set)\((.+)\)$/i', $row['COLUMN_TYPE'], $matches)) {
                $permittedValues = $matches[1];

                if (
                    preg_match_all(
                        "/\\s*'((?:[^']++|'')*+)'\\s*(?:,|\$)/",
                        $permittedValues,
                        $matches,
                        PREG_PATTERN_ORDER
                    )
                ) {
                    $permittedValues = str_replace("''", "'", $matches[1]);
                } else {
                    $permittedValues = [$permittedValues];
                }

                $erratas['permitted_values'] = $permittedValues;
            }

            // Timestamp
            if (str_contains($row['EXTRA'], 'on update')) {
                $erratas['on_update'] = 'current_timestamp()';
            }

            // After MariaDB 10.2.7, the COLUMN_DEFAULT will surround with quotes if is string type.
            // @see https://mariadb.com/kb/en/information-schema-columns-table/
            if (
                is_string($row['COLUMN_DEFAULT'])
                && Str::startsWith($row['COLUMN_DEFAULT'], "'")
                && Str::endsWith($row['COLUMN_DEFAULT'], "'")
            ) {
                $row['COLUMN_DEFAULT'] = Escaper::stripQuote($row['COLUMN_DEFAULT']);
            }

            $columns[$row['COLUMN_NAME']] = [
                'column_name' => $row['COLUMN_NAME'],
                'ordinal_position' => $row['ORDINAL_POSITION'],
                'column_default' => $row['COLUMN_DEFAULT'],
                'is_nullable' => ('YES' === $row['IS_NULLABLE']),
                'data_type' => $row['DATA_TYPE'],
                'character_maximum_length' => $row['CHARACTER_MAXIMUM_LENGTH'],
                'character_octet_length' => $row['CHARACTER_OCTET_LENGTH'],
                'numeric_precision' => $row['NUMERIC_PRECISION'],
                'numeric_scale' => $row['NUMERIC_SCALE'],
                'numeric_unsigned' => str_contains($row['COLUMN_TYPE'], 'unsigned'),
                'comment' => $row['COLUMN_COMMENT'],
                'auto_increment' => $row['EXTRA'] === 'auto_increment',
                'erratas' => $erratas,
            ];
        }

        return $columns;
    }

    /**
     * @inheritDoc
     */
    public function listConstraints(string $table, ?string $schema = null): array
    {
        // JOIN of INFORMATION_SCHEMA table is very slow, we use 3 separate query to get constraints.
        // @see Commit: 4d6e7848268bd9a6add3f7ddc68e879f2f105da5
        // TODO: Test speed with DATABASE()

        // Query 1: TABLE_CONSTRAINTS
        $constraintItems = $this->loadConstraintsStatement($table, $schema)
            ->all()
            ->keyBy('CONSTRAINT_NAME');

        // Query 2: KEY_COLUMN_USAGE
        $query = $this->db->createQuery()
            ->select(
                [
                    'CONSTRAINT_NAME',
                    'COLUMN_NAME',
                    'REFERENCED_TABLE_SCHEMA',
                    'REFERENCED_TABLE_NAME',
                    'REFERENCED_COLUMN_NAME',
                    'REFERENCED_COLUMN_NAME',
                ]
            )
            ->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
            ->where('TABLE_NAME', $this->db->replacePrefix($table))
            ->tap(
                static function (Query $query) use ($schema) {
                    if ($schema !== null) {
                        $query->where('TABLE_SCHEMA', $schema);
                    } else {
                        $query->where('TABLE_SCHEMA', raw('(SELECT DATABASE())'));
                    }
                }
            );

        $kcuGroup = $this->db->prepare($query)->all()->group('CONSTRAINT_NAME');

        // Query 3: REFERENTIAL_CONSTRAINTS
        $query = $this->db->createQuery()
            ->select(
                [
                    'CONSTRAINT_NAME',
                    'MATCH_OPTION',
                    'UPDATE_RULE',
                    'DELETE_RULE',
                ]
            )
            ->from('INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS')
            ->where('TABLE_NAME', $this->db->replacePrefix($table))
            ->tap(
                static function (Query $query) use ($schema) {
                    if ($schema !== null) {
                        $query->where('CONSTRAINT_SCHEMA', $schema);
                    } else {
                        $query->where('CONSTRAINT_SCHEMA', raw('(SELECT DATABASE())'));
                    }
                }
            );

        $rcItems = $this->db->prepare($query)->all()->keyBy('CONSTRAINT_NAME');

        $realName    = null;
        $constraints = [];

        foreach ($constraintItems as $name => $row) {
            $kcuItems = $kcuGroup[$name] ?? new Collection();
            $rcItem   = $rcItems[$name] ?? new Collection();

            $realName = $row['CONSTRAINT_NAME'];
            $isFK     = ('FOREIGN KEY' === $row['CONSTRAINT_TYPE']);

            if ($isFK || $schema !== null) {
                $name = $realName;
            } else {
                $name = $row['TABLE_NAME'] . '_' . $realName;
            }

            $constraints[$name] = [
                'constraint_name' => $realName,
                'constraint_type' => $row['CONSTRAINT_TYPE'],
                'table_name' => $row['TABLE_NAME'],
                'columns' => [],
            ];

            if ($isFK) {
                $constraints[$name]['referenced_table_schema'] = $kcuItems[0]['REFERENCED_TABLE_SCHEMA'];
                $constraints[$name]['referenced_table_name']   = $kcuItems[0]['REFERENCED_TABLE_NAME'];
                $constraints[$name]['referenced_columns']      = [];
                $constraints[$name]['match_option']            = $rcItem['MATCH_OPTION'];
                $constraints[$name]['update_rule']             = $rcItem['UPDATE_RULE'];
                $constraints[$name]['delete_rule']             = $rcItem['DELETE_RULE'];
            }

            foreach ($kcuItems as $kcuItem) {
                $constraints[$name]['columns'][] = $kcuItem['COLUMN_NAME'];

                if ($isFK) {
                    $constraints[$name]['referenced_columns'][] = $kcuItem['REFERENCED_COLUMN_NAME'];
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
            ->group('INDEX_NAME');

        $indexes = [];

        foreach ($indexGroup as $keys) {
            $index = [];
            $name  = $keys[0]['INDEX_NAME'];

            if ($schema === null) {
                $name = $keys[0]['TABLE_NAME'] . '_' . $name;
            }

            $index['table_schema']  = $keys[0]['TABLE_SCHEMA'];
            $index['table_name']    = $keys[0]['TABLE_NAME'];
            $index['is_unique']     = (string) $keys[0]['NON_UNIQUE'] === '0';
            $index['is_primary']    = $keys[0]['INDEX_NAME'] === 'PRIMARY';
            $index['index_name']    = $keys[0]['INDEX_NAME'];
            $index['index_comment'] = $keys[0]['INDEX_COMMENT'];

            $index['columns'] = [];

            foreach ($keys as $key) {
                $index['columns'][$key['COLUMN_NAME']] = [
                    'column_name' => $key['COLUMN_NAME'],
                    'sub_part' => $key['SUB_PART'],
                ];
            }

            $indexes[$name] = $index;
        }

        return $indexes;
    }

    public function getCurrentDatabase(): ?string
    {
        return $this->db->prepare('SELECT DATABASE()')->result();
    }

    public function createSchema(string $name, array $options = []): StatementInterface
    {
        return $this->createDatabase($name, $options);
    }

    public function dropSchema(string $name, array $options = []): StatementInterface
    {
        return $this->dropDatabase($name, $options);
    }

    public function createTable(Schema $schema, bool $ifNotExists = false, array $options = []): StatementInterface
    {
        $defaultOptions = [
            'auto_increment' => 1,
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
        ];

        $options   = array_merge($defaultOptions, $options);
        $columns   = [];
        $table     = $schema->getTable();
        $tableName = $this->db->quoteName($table->schemaName . '.' . $table->getName());
        $primaries = [];

        foreach ($schema->getColumns() as $column) {
            $column = $this->prepareColumn(clone $column);

            if ($column->isPrimary()) {
                $primaries[] = $column;

                // Add AI later after table created.
                $column = clone $column;
                $column->autoIncrement(false);
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
                    'ENGINE' => $options['engine'] ?? 'InnoDB',
                    'AUTO_INCREMENT' => $options['auto_increment'] ?? null,
                    'DEFAULT CHARSET' => $options['charset'] ?? null,
                    'COLLATE' => $options['collate'] ?? null,
                ]
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

            foreach ($primaries as $column) {
                if ($column->isAutoIncrement()) {
                    $this->modifyColumn($table->getName(), $column, $table->schemaName);
                }
            }
        }

        foreach ($schema->getIndexes() as $index) {
            $this->addIndex($table->getName(), $index, $table->schemaName);
        }

        foreach ($schema->getConstraints() as $constraint) {
            $this->addConstraint($table->getName(), $constraint, $table->schemaName);
        }

        return $statement;
    }

    public function getColumnExpression(Column $column): Clause
    {
        return $this->getGrammar()::build(
            $column->getTypeExpression(),
            $column->getNumericUnsigned() ? 'UNSIGNED' : '',
            $column->getIsNullable() ? '' : 'NOT NULL',
            $column->canHasDefaultValue()
                ? 'DEFAULT ' . $this->db->quote($column->getColumnDefault())
                : '',
            $column->isAutoIncrement() ? 'AUTO_INCREMENT' : null,
            $column->getOption('on_update') ? 'ON UPDATE CURRENT_TIMESTAMP' : null,
            $column->getComment() ? 'COMMENT ' . $this->db->quote($column->getComment()) : '',
            implode(' ', $column->getOption('position') ?? []),
            $column->getOption('suffix'),
        );
    }

    public function renameTable(string $from, string $to, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'RENAME TABLE',
                $this->db->quoteName($schema . '.' . $from),
                'TO',
                $this->db->quoteName($schema . '.' . $to),
            )
        );
    }

    public function renameColumn(string $table, string $from, string $to, ?string $schema = null): StatementInterface
    {
        $toColumn = Column::wrap($this->listColumns($table)[$from]);

        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'CHANGE COLUMN',
                $this->db->quoteName($from),
                $this->db->quoteName($to),
                (string) $this->getColumnExpression($toColumn)
            )
        );
    }

    public function addIndex(string $table, Index $index, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->db->createQuery()
                ->alter('TABLE', $schema . '.' . $table)
                ->tap(
                    fn(AlterClause $alter) => $alter->addIndex(
                        $index->indexName,
                        $this->prepareKeyColumns($index->getColumns())
                    )
                )
        );
    }

    protected function getKeyColumnExpression(Column $column): Clause
    {
        $expr = parent::getKeyColumnExpression($column);

        $subParts = $column->getErratas()['sub_parts'] ?? null;
        $length   = $column->getCharacterMaximumLength();

        $types = [
            'varchar',
            'char',
            'text',
            'longtext',
            'mediumtext',
            'json',
        ];

        if (
            $subParts === null
            && (!$length || $length > 150)
            && in_array($column->getDataType(), $types)
        ) {
            $subParts = 150;
        }

        if ($subParts) {
            $expr->setName($expr->getName() . '(' . $subParts . ')');
        }

        return $expr;
    }

    public function dropConstraint(string $table, string $name, ?string $schema = null): StatementInterface
    {
        $constraint = array_values(
            array_filter(
                $this->listConstraints($table, $schema),
                fn($constraint) => $constraint['constraint_name'] === $name
            )
        )[0];

        if ($constraint['constraint_type'] === 'UNIQUE') {
            $action = 'DROP INDEX';
        } else {
            $action = 'DROP ' . $constraint['constraint_type'];
        }

        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                $action,
                $this->db->quoteName($name),
            )
        );
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
}
