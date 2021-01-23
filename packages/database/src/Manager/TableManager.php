<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Manager;

use Windwalker\Database\Schema\DatabaseManager;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Ddl\Index;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Schema\SchemaManager;
use Windwalker\Utilities\Cache\InstanceCacheTrait;

/**
 * The TableManager class.
 */
class TableManager extends AbstractMetaManager
{
    use InstanceCacheTrait;

    public ?string $schemaName = null;

    public ?string $databaseName = null;

    public array $erratas = [];

    /**
     * create
     *
     * @param  callable|Schema  $callback
     * @param  bool             $ifNotExists
     * @param  array            $options
     *
     * @return  static
     */
    public function create(callable|Schema $callback, bool $ifNotExists = true, array $options = []): static
    {
        if ($this->exists()) {
            return $this;
        }

        $this->getPlatform()->createTable(
            $this->callSchema($callback),
            $ifNotExists,
            $options
        );

        $this->getSchema()->reset();

        return $this;
    }

    /**
     * update
     *
     * @param  callable|Schema  $schema
     *
     * @return  static
     *
     * @todo Move update() logic to Platform class.
     */
    public function update($schema): static
    {
        $schema = $this->callSchema($schema);

        $this->reset();
        $platform = $this->getPlatform();

        foreach ($schema->getColumns() as $column) {
            $column = $platform->prepareColumn(clone $column);

            if ($this->hasColumn($column->getColumnName())) {
                $this->modifyColumn($column);
            } else {
                $this->addColumn($column);
            }
        }

        $this->reset();

        foreach ($schema->getIndexes() as $index) {
            if ($this->hasIndex($index->indexName)) {
                $this->dropIndex($index->indexName);
            }

            $this->addIndex($index);
        }

        foreach ($schema->getConstraints() as $constraint) {
            if ($this->hasConstraint($constraint->constraintName)) {
                $this->dropConstraint($constraint->constraintName);
            }

            $this->addConstraint($constraint);
        }

        return $this->reset();
    }

    /**
     * save
     *
     * @param  callable|Schema  $schema
     * @param  bool             $ifNotExists
     * @param  array            $options
     *
     * @return  static
     */
    public function save($schema, bool $ifNotExists = true, array $options = []): static
    {
        $schema = $this->callSchema($schema);

        if ($this->exists()) {
            $this->update($schema);
        } else {
            $this->create($schema, $ifNotExists, $options);
        }

        return $this->reset();
    }

    public function drop(): static
    {
        $this->getPlatform()->dropTable(
            $this->getName(),
            $this->schemaName
        );

        return $this->reset();
    }

    /**
     * exists
     *
     * @return  bool
     */
    public function exists(): bool
    {
        return isset($this->getPlatform()->listTables($this->schemaName, true)[$this->getName()]);
    }

    public function rename(string $newName, bool $returnNew = true): static
    {
        $this->getPlatform()->renameTable($this->getName(), $newName);

        if ($returnNew) {
            return $this->db->getTable($newName, true);
        }

        $this->name = $newName;

        return $this;
    }

    /**
     * Method to truncate a table.
     *
     * @return  static
     *
     * @throws  \RuntimeException
     * @since   2.0
     */
    public function truncate(): static
    {
        $this->getPlatform()->truncateTable($this->getName(), $this->schemaName);

        return $this;
    }

    /**
     * getDetail
     *
     * @return  array
     */
    public function getDetail(): array
    {
        return $this->getPlatform()->getTableDetail($this->getName());
    }

    /**
     * Get table columns.
     *
     * @param  bool  $refresh
     *
     * @return array Table columns with type.
     */
    public function getColumnNames(bool $refresh = false): array
    {
        return array_keys($this->getColumns($refresh));
    }

    /**
     * getColumnDetails
     *
     * @param  bool  $refresh
     *
     * @return Column[]
     */
    public function getColumns(bool $refresh = false): array
    {
        return $this->once(
            'columns',
            fn() => Column::wrapList(
                $this->getPlatform()
                    ->listColumns(
                        $this->getName(),
                        $this->schemaName
                    )
            ),
            $refresh
        );
    }

    /**
     * getColumn
     *
     * @param  string  $name
     *
     * @return Column|null
     */
    public function getColumn(string $name): ?Column
    {
        return $this->getColumns()[$name] ?? null;
    }

    /**
     * hasColumn
     *
     * @param  string  $name
     *
     * @return  bool
     */
    public function hasColumn(string $name): bool
    {
        return isset($this->getColumns()[$name]);
    }

    /**
     * addColumn
     *
     * @param  string|Column  $column
     * @param  string         $dataType
     * @param  bool           $isNullable
     * @param  null           $columnDefault
     * @param  array          $options
     *
     * @return static
     */
    public function addColumn(
        string|Column $column = '',
        string $dataType = 'char',
        bool $isNullable = false,
        $columnDefault = null,
        array $options = []
    ): static {
        if (!$column instanceof Column) {
            $column = new Column($column, $dataType, $isNullable, $columnDefault, $options);
        }

        if (!$this->hasColumn($column->getColumnName())) {
            $this->getPlatform()->addColumn($this->getName(), $column, $this->schemaName);
        }

        return $this;
    }

    public function dropColumn(string|array $names): static
    {
        $names = (array) $names;
        $platform = $this->getPlatform();
        $constraints = $this->getConstraints();

        foreach ($names as $name) {
            if (!$this->hasColumn($name)) {
                continue;
            }

            foreach ($constraints as $key => $constraint) {
                if (array_key_exists($name, $constraint->getColumns())) {
                    $this->dropConstraint($constraint->constraintName);

                    unset($constraints[$key]);
                }
            }

            $platform->dropColumn($this->getName(), $name, $this->schemaName);
        }

        return $this;
    }

    public function modifyColumn(
        string|array|Column $column = '',
        string $dataType = 'char',
        bool $isNullable = false,
        $columnDefault = null,
        array $options = []
    ): static {
        if (!$column instanceof Column) {
            $column = new Column($column, $dataType, $isNullable, $columnDefault, $options);
        }

        $this->getPlatform()->modifyColumn($this->getName(), $column, $this->schemaName);

        return $this;
    }

    public function renameColumn(string $from, string $to): static
    {
        if ($this->hasColumn($from)) {
            $this->getPlatform()->renameColumn($this->getName(), $from, $to, $this->schemaName);
        }

        return $this;
    }

    public function addIndex($columns = [], ?string $name = null, array $options = []): static
    {
        if (!$columns instanceof Index) {
            $tableColumns = $this->getColumns();
            $index = new Index($name, $this->getName());
            $index->columns((array) $columns)
                ->bind($options);

            foreach ($index->getColumns() as $column) {
                if (isset($tableColumns[$column->getColumnName()])) {
                    $column->dataType($tableColumns[$column->getColumnName()]->getDataType());
                }
            }
        } else {
            $index = $columns;
        }

        $name ??= $index->indexName ??= Schema::createKeyName(
            $this->getName(),
            array_keys($index->getColumns())
        );
        $index->name($name);

        if ($this->hasIndex($name)) {
            return $this;
        }

        $this->getPlatform()->addIndex($this->getName(), $index, $this->schemaName);

        return $this;
    }

    public function dropIndex(array|string $names): static
    {
        $platform = $this->getPlatform();

        foreach ((array) $names as $name) {
            if (!$this->hasIndex($name)) {
                $platform->dropIndex($this->getName(), $name, $this->schemaName);
            }
        }

        return $this;
    }

    /**
     * getIndexes
     *
     * @return  Index[]
     */
    public function getIndexes(): array
    {
        return $this->once(
            'indexes',
            fn () => Index::wrapList(
                $this->getPlatform()->listIndexes($this->getName(), $this->schemaName),
                'index_name'
            )
        );
    }

    public function getIndex(string $name): ?Index
    {
        return $this->getIndexes()[$name] ?? null;
    }

    public function hasIndex($name): bool
    {
        return isset($this->getIndexes()[$name]);
    }

    public function addConstraint(
        array|string|Constraint $columns = [],
        string $type = Constraint::TYPE_UNIQUE,
        ?string $name = null,
        array $options = []
    ): static {
        if (!$columns instanceof Constraint) {
            $tableColumns = $this->getColumns();

            $constraint = new Constraint($type, $name, $this->getName());
            $constraint->columns((array) $columns)
                ->bind($options);

            foreach ($constraint->getColumns() as $column) {
                if (isset($tableColumns[$column->getColumnName()])) {
                    $column->dataType($tableColumns[$column->getColumnName()]->getDataType() ?? '');
                }
            }
        } else {
            $constraint = $columns;
        }

        $name ??= $constraint->constraintName ??= Schema::createKeyName(
            $this->getName(),
            array_keys($constraint->getColumns()),
            'ct'
        );
        $constraint->name($name);

        if ($this->hasConstraint($name)) {
            return $this;
        }

        $this->getPlatform()->addConstraint($this->getName(), $constraint, $this->schemaName);

        return $this;
    }

    public function hasConstraint(string $name): bool
    {
        return isset($this->getConstraints()[$name]);
    }

    /**
     * getConstraints
     *
     * @return  Constraint[]
     */
    public function getConstraints(): array
    {
        return $this->once(
            'constraints',
            fn () => Constraint::wrapList(
                $this->getPlatform()->listConstraints($this->getName(), $this->schemaName),
                'constraint_name'
            )
        );
    }

    public function getConstraint(string $name): ?Constraint
    {
        return $this->getConstraints()[$name] ?? null;
    }

    public function dropConstraint(string|array $names): static
    {
        $platform = $this->getPlatform();

        foreach ((array) $names as $name) {
            if ($this->hasConstraint($name)) {
                $platform->dropConstraint($this->getName(), $name, $this->schemaName);
            }
        }

        return $this;
    }

    /**
     * @param  string  $name
     *
     * @return  static  Return self to support chaining.
     */
    public function setName(?string $name): static
    {
        if ($name !== null) {
            $names = explode('.', $name);

            if (\count($names) >= 2) {
                [$schema, $name] = $names;

                $this->schemaName = $schema;
            }
        }

        parent::setName($name);

        return $this;
    }

    /**
     * getSchema
     *
     * @param  bool  $new
     *
     * @return  SchemaManager
     */
    public function getSchema(bool $new = false): SchemaManager
    {
        return $this->db->getSchema($this->schemaName, $new);
    }

    public function createSchemaObject(): Schema
    {
        return new Schema($this);
    }

    protected function callSchema(callable|Schema $callback): Schema
    {
        if (is_callable($callback)) {
            $callback($schema = $this->createSchemaObject());
        } else {
            $schema = $callback;
        }

        if (!$schema instanceof Schema) {
            throw new \InvalidArgumentException('Argument 1 should be Schema object.');
        }

        return $schema;
    }

    public function getDatabase(bool $new = false): DatabaseManager
    {
        return $this->db->getDatabase($this->databaseName, $new);
    }

    /**
     * @return array
     */
    public function getErratas(): array
    {
        return $this->erratas;
    }

    /**
     * @param  array  $erratas
     *
     * @return  static  Return self to support chaining.
     */
    public function setErratas(array $erratas): static
    {
        $this->erratas = $erratas;

        return $this;
    }

    /**
     * reset
     *
     * @return  static
     */
    public function reset(): static
    {
        $this->cacheReset();

        return $this;
    }
}
