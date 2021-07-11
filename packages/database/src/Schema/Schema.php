<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Schema;

use InvalidArgumentException;
use Windwalker\Database\Manager\TableManager;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Ddl\Index;

/**
 * The Schema class.
 *
 * @method  Column  bigint(string $name)
 * @method  Column  bit(string $name)
 * @method  Column  char(string $name)
 * @method  Column  datetime(string $name)
 * @method  Column  date(string $name)
 * @method  Column  decimal(string $name)
 * @method  Column  double(string $name)
 * @method  Column  float(string $name)
 * @method  Column  integer(string $name)
 * @method  Column  longtext(string $name)
 * @method  Column  primary(string $name)
 * @method  Column  text(string $name)
 * @method  Column  timestamp(string $name)
 * @method  Column  tinyint(string $name)
 * @method  Column  varchar(string $name)
 * @method  Column  json(string $name)
 *
 * @since  2.1.8
 */
class Schema
{
    /**
     * @var  Column[]
     */
    protected array $columns = [];

    /**
     * @var Index[]
     */
    protected array $indexes = [];

    /**
     * @var Constraint[]
     */
    protected array $constraints = [];

    protected TableManager $table;

    public function __construct(TableManager $table)
    {
        $this->table = $table;
    }

    public function addColumn(Column|string $column): Column
    {
        if (is_string($column) && class_exists($column)) {
            $column = new $column();
        }

        if (!$column instanceof Column) {
            throw new InvalidArgumentException(__METHOD__ . ' argument 1 need Column instance.');
        }

        $this->columns[$column->getColumnName()] = $column;

        return $column;
    }

    public function addConstraint(string|Constraint $constraint, string $name): Constraint
    {
        if (is_string($constraint)) {
            $constraint = new Constraint($constraint, $name, $this->table->getName());
        }

        $this->constraints[$constraint->constraintName] = $constraint;

        return $constraint;
    }

    public function addIndex(array|string|Index $columns, ?string $name = null, ?string $comment = null): Index
    {
        if (!$columns instanceof Index) {
            $index = new Index('');
            $index->tableName = $this->table->getName();
            $index->indexComment = $comment;

            if (is_string($columns)) {
                $columns = (array) $columns;
            }

            $index->columns($columns);
        } else {
            $index = $columns;
        }

        if (!$name) {
            $name = static::createKeyName($this->table->getName(), array_keys($index->getColumns()));
        }

        $index->name($name);

        $this->indexes[$name] = $index;

        return $index;
    }

    public function addUniqueKey(array|string $columns, ?string $name = null): Constraint
    {
        $columns = (array) $columns;

        if (!$name) {
            $name = static::createKeyName($this->table->getName(), $columns);
        }

        return $this->addConstraint(Constraint::TYPE_UNIQUE, $name)
            ->columns($columns);
    }

    public function addPrimaryKey(array|string $columns): Constraint
    {
        $columns = (array) $columns;

        $name = 'pk_' . $this->table->getName();

        return $this->addConstraint(Constraint::TYPE_PRIMARY_KEY, $name)
            ->columns($columns);
    }

    public function addForeignKey(
        array|string $columns,
        ?string $refTable = null,
        array|string|null $refColumns = null
    ): Constraint {
        $columns = (array) $columns;

        $constraint = $this->addConstraint(Constraint::TYPE_PRIMARY_KEY, 'PRIMARY')
            ->columns($columns);

        if ($refTable) {
            $constraint->referencedTableName = $refTable;
        }

        if ($refColumns) {
            $constraint->referencedColumns((array) $refColumns);
        }

        return $constraint;
    }

    public static function createKeyName(string $tableName, array $columns, string $prefix = 'idx'): string
    {
        $columns = array_map(
            static fn($col) => explode('(', $col)[0],
            $columns
        );

        return sprintf(
            '%s_%s_%s',
            $prefix,
            trim($tableName, '#_'),
            implode('_', $columns)
        );
    }

    public function getTable(): TableManager
    {
        return $this->table;
    }

    public function setTable(TableManager $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return  Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Method to set property columns
     *
     * @param  Column[]  $columns
     *
     * @return  static  Return self to support chaining.
     */
    public function setColumns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function getDateFormat(): string
    {
        return $this->getTable()->getDb()->getDateFormat();
    }

    public function getNullDate(): string
    {
        return $this->getTable()->getDb()->getNullDate();
    }

    public function __call(string $name, array $args): Column
    {
        $column = array_shift($args);

        $column = $this->addColumn(new Column($column, $name));

        if ($name === 'primary') {
            $column->dataType('integer')
                ->autoIncrement(true)
                ->primary(true);
        }

        if ($name === 'primaryBigint') {
            $column->dataType('bigint')
                ->autoIncrement(true)
                ->primary(true);
        }

        return $column;
    }

    /**
     * @return Index[]
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * @param  Index[]  $indexes
     *
     * @return  static  Return self to support chaining.
     */
    public function setIndexes(array $indexes): static
    {
        $this->indexes = $indexes;

        return $this;
    }

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * @param  Constraint[]  $constraints
     *
     * @return  static  Return self to support chaining.
     */
    public function setConstraints(array $constraints): static
    {
        $this->constraints = $constraints;

        return $this;
    }
}
