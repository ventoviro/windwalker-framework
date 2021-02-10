<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Strategy;

use Windwalker\Database\DatabaseAdapter;
use Windwalker\Query\Query;

/**
 * The AbstractQueryAction class.
 *
 * @property-read DatabaseAdapter $db
 */
abstract class AbstractQueryStrategy extends Query
{
    /**
     * @inheritDoc
     */
    public function __construct(DatabaseAdapter $db, $grammar = null)
    {
        parent::__construct($db, $grammar ?? $db->getPlatform()->getGrammar());
    }

    public function getDb(): DatabaseAdapter
    {
        return $this->getEscaper()->getConnection();
    }

    public function __call(string $name, array $args): mixed
    {
        if ($name === 'db') {
            return $this->getDb();
        }

        return parent::__call($name, $args);
    }
}
