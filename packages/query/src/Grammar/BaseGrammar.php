<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;

/**
 * The BaseGrammar class.
 */
class BaseGrammar extends AbstractGrammar
{
    /**
     * @inheritDoc
     */
    public function listDatabases(): Query
    {
        return $this->createQuery();
    }

    /**
     * @inheritDoc
     */
    public function listTables(?string $schema = null): Query
    {
        return $this->createQuery();
    }

    /**
     * @inheritDoc
     */
    public function listViews(?string $schema = null): Query
    {
        return $this->createQuery();
    }

    public function compileJsonSelector(Query $query,
        string $column,
        array $paths,
        bool $unQuoteLast = true,
        bool $instant = false
    ): Clause
    {
        throw new \LogicException('This DB does not support JSON.');
    }
}
