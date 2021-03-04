<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Query\Concern;

use Windwalker\Attributes\AttributesResolver;
use Windwalker\Database\Exception\DatabaseQueryException;
use Windwalker\ORM\Attributes\Table;
use Windwalker\Query\Clause\AsClause;
use Windwalker\Query\Clause\JoinClause;

/**
 * Trait ReflectConcernTrait
 */
trait ReflectConcernTrait
{
    /**
     * getAllTables
     *
     * @return AsClause[]
     */
    public function getAllTables(): array
    {
        /** @var AsClause[] $froms */
        $froms = $this->from?->getElements() ?? [];

        /** @var JoinClause[] $joins */
        $joins = $this->join?->getElements() ?? [];

        $tables = [];

        foreach ($froms as $from) {
            $tables['FROM'][$from->getAlias()] = $from;
        }

        foreach ($joins as $join) {
            $joinTable = $join->getTable();
            $tables[$join->getPrefix()][$joinTable->getAlias()] = $joinTable;
        }

        return $tables;
    }

    public static function convertClassToTable(string $name, ?string &$alias = null): string
    {
        if (!str_contains($name, '\\') || !class_exists($name)) {
            return $name;
        }

        $ref = new \ReflectionClass($name);
        $tableAttr = $ref->getAttributes( Table::class, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

        if (!$tableAttr) {
            throw new DatabaseQueryException(
                sprintf(
                    'Value or column is class name but %s Attribute not assigned.',
                    Table::class
                )
            );
        }

        /** @var Table $table */
        $table = $tableAttr->newInstance();
        $alias = $table->getAlias();

        return $table->getName();
    }
}
