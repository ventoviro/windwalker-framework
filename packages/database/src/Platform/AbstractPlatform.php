<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Platform;

use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Driver\TransactionDriverInterface;
use Windwalker\Database\Platform\Type\DataType;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Ddl\Index;
use Windwalker\Database\Schema\Schema;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Query;

use function Windwalker\Query\clause;

/**
 * The AbstractPlatform class.
 */
abstract class AbstractPlatform
{
    public const MYSQL = 'MySQL';

    public const POSTGRESQL = 'PostgreSQL';

    public const SQLSERVER = 'SQLServer';

    public const SQLITE = 'SQLite';

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string|null
     */
    protected static ?string $defaultSchema = null;

    protected int $depth = 0;

    protected ?Query $query = null;

    protected ?AbstractGrammar $grammar = null;

    protected ?DatabaseAdapter $db = null;

    protected ?DataType $dataType = null;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public static function createPlatform(string $platform, DatabaseAdapter $db)
    {
        $class = __NAMESPACE__ . '\\' . DatabaseFactory::getPlatformName($platform) . 'Platform';

        return new $class($db);
    }

    /**
     * @return string|null
     */
    public static function getDefaultSchema(): ?string
    {
        return static::$defaultSchema;
    }

    /**
     * AbstractPlatform constructor.
     *
     * @param  AbstractGrammar|null  $grammar
     */
    public function __construct(?AbstractGrammar $grammar = null)
    {
        $this->grammar = $grammar ?? AbstractGrammar::create($this->name);
    }

    public function getGrammar(): AbstractGrammar
    {
        return $this->grammar;
    }

    public function createQuery(mixed $escaper = null): Query
    {
        return new Query($escaper ?? $this->db, $this->grammar);
    }

    abstract public function listDatabasesQuery(): Query;

    abstract public function listSchemaQuery(): Query;

    abstract public function listTablesQuery(?string $schema): Query;

    abstract public function listViewsQuery(?string $schema): Query;

    abstract public function listColumnsQuery(string $table, ?string $schema): Query;

    abstract public function listConstraintsQuery(string $table, ?string $schema): Query;

    abstract public function listIndexesQuery(string $table, ?string $schema): Query;

    public function listDatabases(): array
    {
        return $this->db->prepare(
            $this->listDatabasesQuery()
        )
            ->loadColumn()
            ->dump();
    }

    public function listSchemas(): array
    {
        return $this->listSchemaQuery()
            ->loadColumn()
            ->dump();
    }

    /**
     * @inheritDoc
     */
    public function listTables(?string $schema = null, bool $includeViews = false): array
    {
        $tables = $this->listTablesQuery($schema)
            ->all()
            ->keyBy('TABLE_NAME')
            ->dump(true);

        if ($includeViews) {
            $tables = array_merge(
                $tables,
                $this->listViews($schema)
            );
        }

        return $tables;
    }

    /**
     * @inheritDoc
     */
    public function listViews(?string $schema = null): array
    {
        $this->listViewsQuery($schema)->render(true);

        return $this->listViewsQuery($schema)
            ->all()
            ->keyBy('TABLE_NAME')
            ->dump(true);
    }

    /**
     * @inheritDoc
     */
    abstract public function listColumns(string $table, ?string $schema = null): array;

    /**
     * @inheritDoc
     */
    public function loadColumnsStatement(string $table, ?string $schema = null): StatementInterface
    {
        return $this->listColumnsQuery($table, $schema)->prepareStatement();
    }

    /**
     * @inheritDoc
     */
    abstract public function listConstraints(string $table, ?string $schema = null): array;

    /**
     * @inheritDoc
     */
    public function loadConstraintsStatement(string $table, ?string $schema = null): StatementInterface
    {
        return $this->listConstraintsQuery($table, $schema)->prepareStatement();
    }

    /**
     * @inheritDoc
     */
    abstract public function listIndexes(string $table, ?string $schema = null): array;

    /**
     * @inheritDoc
     */
    public function loadIndexesStatement(string $table, ?string $schema = null): StatementInterface
    {
        return $this->listIndexesQuery($table, $schema)->prepareStatement();
    }

    abstract public function getCurrentDatabase(): ?string;

    public function createDatabase(string $name, array $options = []): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()
                ::build(
                    'CREATE DATABASE',
                    !empty($options['if_not_exists']) ? 'IF NOT EXISTS' : null,
                    $this->db->quoteName($name),
                    'CHARACTER SET=' . ($options['charset'] ?? 'utf8mb4'),
                    'COLLATE=' . ($options['collation'] ?? 'utf8mb4_unicode_ci'),
                )
        );
    }

    public function dropDatabase(string $name, array $options = []): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()
                ::build(
                    'DROP DATABASE',
                    !empty($options['if_exists']) ? 'IF EXISTS' : null,
                    $this->db->quoteName($name)
                )
        );
    }

    public function createSchema(string $name, array $options = []): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()
                ::build(
                    'CREATE SCHEMA',
                    !empty($options['if_not_exists']) ? 'IF NOT EXISTS' : null,
                    $this->db->quoteName($name)
                )
        );
    }

    /**
     * dropSchema
     *
     * @param  string  $name
     * @param  array   $options
     *
     * @return  StatementInterface
     */
    public function dropSchema(string $name, array $options = []): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()
                ::build(
                    'DROP SCHEMA',
                    $this->db->quoteName($name)
                )
        );
    }

    abstract public function createTable(
        Schema $schema,
        bool $ifNotExists = false,
        array $options = []
    ): StatementInterface;

    public function getColumnExpression(Column $column): Clause
    {
        return $this->getGrammar()::build(
            $column->getTypeExpression(),
            $column->getIsNullable() ? '' : 'NOT NULL',
            $column->canHasDefaultValue()
                ? 'DEFAULT ' . $this->db->quote($column->getColumnDefault())
                : '',
            $column->getOption('suffix')
        );
    }

    public function dropTable(string $table, ?string $schema = null, $suffix = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'DROP TABLE',
                'IF EXISTS',
                $this->db->quoteName($schema . '.' . $table),
                $suffix
            )
        );
    }

    public function renameTable(string $from, string $to, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $from),
                'RENAME TO',
                $this->db->quoteName($schema . '.' . $to),
            )
        );
    }

    public function truncateTable(string $table, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'TRUNCATE TABLE',
                $this->db->quoteName($schema . '.' . $table)
            )
        );
    }

    public function getTableDetail(string $table, ?string $schema = null): ?array
    {
        return $this->listTables($schema, true)[$table] ?? null;
    }

    public function addColumn(string $table, Column $column, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'ADD COLUMN',
                $this->db->quoteName($column->getColumnName()),
                (string) $this->getColumnExpression($column)
            )
        );
    }

    public function dropColumn(string $table, string $name, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'DROP COLUMN',
                $this->db->quoteName($name),
            )
        );
    }

    public function modifyColumn(string $table, Column $column, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'MODIFY COLUMN',
                $this->db->quoteName($column->getColumnName()),
                (string) $this->getColumnExpression($column)
            )
        );
    }

    abstract public function renameColumn(
        string $table,
        string $from,
        string $to,
        ?string $schema = null
    ): StatementInterface;

    public function addIndex(string $table, Index $index, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'CREATE INDEX',
                $this->db->quoteName($index->indexName),
                'ON',
                $this->db->quoteName($schema . '.' . $table),
                (string) clause('()', $this->prepareKeyColumns($index->getColumns()), ','),
            )
        );
    }

    public function dropIndex(string $table, string $name, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'DROP INDEX',
                $this->db->quoteName($name),
            )
        );
    }

    public function addConstraint(string $table, Constraint $constraint, ?string $schema = null): StatementInterface
    {
        $alter = $this->db->createQuery()
            ->alter('TABLE', $schema . '.' . $table);

        if ($constraint->constraintType === Constraint::TYPE_PRIMARY_KEY) {
            $alter->addPrimaryKey(
                $constraint->constraintName,
                $this->prepareKeyColumns($constraint->getColumns())
            );
        } elseif ($constraint->constraintType === Constraint::TYPE_UNIQUE) {
            $alter->addUniqueKey(
                $constraint->constraintName,
                $this->prepareKeyColumns($constraint->getColumns())
            );
        } elseif ($constraint->constraintType === Constraint::TYPE_FOREIGN_KEY) {
            $alter->addForeignKey(
                $constraint->constraintName,
                $this->prepareKeyColumns($constraint->getColumns()),
                $this->prepareKeyColumns($constraint->getReferencedColumns()),
                $constraint->updateRule,
                $constraint->deleteRule,
            );
        }

        return $this->db->execute((string) $alter);
    }

    protected function prepareKeyColumns(array $columns): array
    {
        return array_map(fn(Column $col) => $this->getKeyColumnExpression($col), $columns);
    }

    protected function getKeyColumnExpression(Column $column): Clause
    {
        $expr = clause($this->db->quoteName($column->getColumnName()), [], ' ');

        if ($column->getOption('sort')) {
            $expr->append($column->getOption('sort'));
        }

        return $expr;
    }

    public function dropConstraint(string $table, string $name, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'DROP CONSTRAINT',
                $this->db->quoteName($name),
            )
        );
    }

    public function prepareColumn(Column $column): Column
    {
        $typeMapper = $this->getDataType();

        $type = $typeMapper::getAvailableType($column->getDataType());
        $length = $column->getLengthExpression() ?: $typeMapper::getLength($type);

        $column->length($length);
        $column->dataType($type);

        // Prepare default value
        return $this->prepareDefaultValue($column);
    }

    /**
     * prepareDefaultValue
     *
     * @param  Column  $column
     *
     * @return  Column
     */
    public function prepareDefaultValue(Column $column): Column
    {
        $typeMapper = $this->getDataType();

        if (
            $column->getColumnDefault() === false
            || ($column->getColumnDefault() === null && !$column->getIsNullable())
        ) {
            $default = $typeMapper::getDefaultValue($column->getDataType());

            $column->defaultValue($default);
        }

        if ($column->isPrimary() || $column->isAutoIncrement()) {
            $column->defaultValue(false);
        }

        return $column;
    }

    /**
     * start
     *
     * @return  static
     */
    public function transactionStart(): static
    {
        $driver = $this->db->getDriver();

        if ($driver instanceof TransactionDriverInterface) {
            $driver->transactionStart();
        } else {
            $this->db->execute('BEGIN;');
        }

        $this->depth++;

        return $this;
    }

    /**
     * commit
     *
     * @return  static
     */
    public function transactionCommit(): static
    {
        $driver = $this->db->getDriver();

        if ($driver instanceof TransactionDriverInterface) {
            $driver->transactionCommit();
        } else {
            $this->db->execute('COMMIT;');
        }

        $this->depth--;

        return $this;
    }

    /**
     * rollback
     *
     * @return  static
     */
    public function transactionRollback(): static
    {
        $driver = $this->db->getDriver();

        if ($driver instanceof TransactionDriverInterface) {
            $driver->transactionRollback();
        } else {
            $this->db->execute('ROLLBACK;');
        }

        $this->depth--;

        return $this;
    }

    /**
     * transaction
     *
     * @param  callable  $callback
     * @param  bool      $autoCommit
     * @param  bool      $enabled
     *
     * @return  mixed
     *
     * @throws \Throwable
     */
    public function transaction(callable $callback, bool $autoCommit = true, bool $enabled = true): mixed
    {
        if (!$enabled) {
            return $callback($this->db, $this);
        }

        $this->transactionStart();

        try {
            $result = $callback($this->db, $this);

            if ($autoCommit) {
                $this->transactionCommit();
            }

            return $result;
        } catch (\Throwable $e) {
            $this->transactionRollback();

            throw $e;
        }
    }

    public function getDataType(): DataType
    {
        if (!$this->dataType) {
            $class = 'Windwalker\Database\Platform\Type\\' . $this->getName() . 'DataType';

            if (!class_exists($class)) {
                $class = DataType::class;
            }

            $this->dataType = new $class();
        }

        return $this->dataType;
    }

    /**
     * @param  DatabaseAdapter|null  $db
     *
     * @return  static  Return self to support chaining.
     */
    public function setDbAdapter(?DatabaseAdapter $db): static
    {
        $this->db = $db;

        return $this;
    }
}
