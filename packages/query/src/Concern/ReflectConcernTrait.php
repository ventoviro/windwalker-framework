<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Query\Concern;

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
}
