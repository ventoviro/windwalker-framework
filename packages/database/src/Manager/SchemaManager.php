<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Manager;

use Windwalker\Attributes\AttributesAccessor;
use Windwalker\Database\Schema\Ddl\Table;
use Windwalker\ORM\Attributes\Table as TableAttr;
use Windwalker\Utilities\Cache\InstanceCacheTrait;

/**
 * The SchemaManager class.
 */
class SchemaManager extends AbstractMetaManager
{
    use InstanceCacheTrait;

    public function create(array $options = []): static
    {
        if (!$this->exists()) {
            $this->getPlatform()->createSchema($this->getName(), $options);
        }

        return $this;
    }

    public function drop(array $options = []): static
    {
        if ($this->exists()) {
            $this->getPlatform()->dropSchema($this->getName(), $options);
        }

        return $this;
    }

    public function exists(): bool
    {
        return in_array($this->getName(), $this->getPlatform()->listSchemas(), true);
    }

    public function getTable(string $name, bool $new = false): TableManager
    {
        if (class_exists($name)) {
            /** @var TableAttr $tableAttr */
            $tableAttr = AttributesAccessor::getFirstAttributeInstance(
                $name,
                TableAttr::class,
                \ReflectionAttribute::IS_INSTANCEOF
            );

            if ($tableAttr) {
                $name = $tableAttr->getName();
            }
        }

        return $this->once('table.manager.' . $name, fn() => new TableManager($name, $this->db), $new);
    }

    /**
     * @param  bool  $includeViews
     * @param  bool  $refresh
     *
     * @return  Table[]
     */
    public function getTables(bool $includeViews = false, bool $refresh = false): array
    {
        $platform = $this->db->getPlatform();

        $tables = $this->once(
            'tables',
            fn() => Table::wrapList(
                $platform->listTables($this->getName())
            ),
            $refresh
        );

        if ($includeViews) {
            $tables = array_merge($tables, $this->getViews());
        }

        return $tables;
    }

    public function getViews(bool $refresh = false)
    {
        return $this->once(
            'views',
            fn() => Table::wrapList(
                $this->getPlatform()->listViews($this->getName())
            ),
            $refresh
        );
    }

    public function getTableDetail(string $table, bool $includeViews = false): ?Table
    {
        return $this->getTables($includeViews)[$table];
    }

    public function hasTable(string $table): bool
    {
        return in_array($this->db->replacePrefix($table), $this->getTables(), true);
    }

    public function reset(): static
    {
        $this->tables = null;
        $this->views  = null;

        return $this;
    }
}
