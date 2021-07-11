<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Schema\Ddl;

use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The Constraint class.
 */
class Constraint
{
    use WrappableTrait;
    use OptionAccessTrait;

    public const TYPE_PRIMARY_KEY = 'PRIMARY KEY';

    public const TYPE_UNIQUE = 'UNIQUE';

    public const TYPE_FOREIGN_KEY = 'FOREIGN KEY';

    public const TYPE_CHECK = 'CHECK';

    public ?string $constraintName = null;

    public ?string $constraintType = null;

    public ?string $tableName = null;

    public ?string $referencedTableSchema = null;

    public ?string $referencedTableName = null;

    public ?string $matchOption = null;

    public ?string $updateRule = null;

    public ?string $deleteRule = null;

    /**
     * @var Column[]
     */
    public array $columns = [];

    /**
     * @var Column[]
     */
    public array $referencedColumns = [];

    public function __construct(
        ?string $constraintType = null,
        ?string $constraintName = null,
        ?string $tableName = null
    ) {
        $this->constraintName = $constraintName;
        $this->tableName = $tableName;
        $this->constraintType = $constraintType;
    }

    /**
     * @param  string  $constraintName
     *
     * @return  static  Return self to support chaining.
     */
    public function name(string $constraintName): static
    {
        $this->constraintName = $constraintName;

        return $this;
    }

    /**
     * @param  string  $constraintType
     *
     * @return  static  Return self to support chaining.
     */
    public function type(string $constraintType): static
    {
        $this->constraintType = $constraintType;

        return $this;
    }

    /**
     * @param  string  $tableName
     *
     * @return  static  Return self to support chaining.
     */
    public function tableName(string $tableName): static
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @param  string|null  $referencedTableSchema
     *
     * @return  static  Return self to support chaining.
     */
    public function referencedSchema(?string $referencedTableSchema): static
    {
        $this->referencedTableSchema = $referencedTableSchema;

        return $this;
    }

    /**
     * @param  string|null  $referencedTableName
     *
     * @return  static  Return self to support chaining.
     */
    public function referencedTableName(?string $referencedTableName): static
    {
        $this->referencedTableName = $referencedTableName;

        return $this;
    }

    /**
     * @param  string|null  $matchOption
     *
     * @return  static  Return self to support chaining.
     */
    public function matchOption(?string $matchOption): static
    {
        $this->matchOption = $matchOption;

        return $this;
    }

    /**
     * @param  string|null  $updateRule
     *
     * @return  static  Return self to support chaining.
     */
    public function onUpdate(?string $updateRule): static
    {
        $this->updateRule = $updateRule;

        return $this;
    }

    /**
     * @param  string|null  $deleteRule
     *
     * @return  static  Return self to support chaining.
     */
    public function onDelete(?string $deleteRule): static
    {
        $this->deleteRule = $deleteRule;

        return $this;
    }

    public function referencedTo(string $table, array|string $columns): static
    {
        $this->referencedTableName = $table;

        $this->referencedColumns($columns);

        return $this;
    }

    /**
     * @param  Column[]|string[]  $referencedColumns
     *
     * @return  static  Return self to support chaining.
     */
    public function referencedColumns(array $referencedColumns): static
    {
        $cols = [];

        foreach ($referencedColumns as $column) {
            if (!$column instanceof Column) {
                $column = new Column($column);
            } else {
                $column = clone $column;
            }

            $cols[$column->getColumnName()] = $column;
        }

        $this->referencedColumns = $cols;

        return $this;
    }

    /**
     * columns
     *
     * @param  Column[]|string[]  $columns
     *
     * @return  $this
     */
    public function columns(array $columns): static
    {
        $cols = [];

        foreach ($columns as $column) {
            if (!$column instanceof Column) {
                $column = new Column($column);
            }

            $cols[$column->getColumnName()] = $column;
        }

        $this->columns = $cols;

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return Column[]
     */
    public function getReferencedColumns(): array
    {
        return $this->referencedColumns;
    }
}
