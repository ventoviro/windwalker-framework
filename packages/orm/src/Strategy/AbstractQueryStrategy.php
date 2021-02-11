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
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\ORM\ORM;
use Windwalker\Query\Query;

/**
 * The AbstractQueryAction class.
 *
 * @property-read DatabaseAdapter $db
 * @property-read ORM $orm
 */
abstract class AbstractQueryStrategy extends Query
{
    /**
     * @inheritDoc
     */
    public function __construct(ORM $orm, $grammar = null)
    {
        parent::__construct($orm, $grammar ?? $orm->getDb()->getPlatform()->getGrammar());
    }

    public function getDb(): DatabaseAdapter
    {
        return $this->getORM()->getDb();
    }

    public function getORM(): ORM
    {
        return $this->getEscaper()->getConnection();
    }

    public function __get(string $name)
    {
        if ($name === 'db') {
            return $this->getDb();
        }

        if ($name === 'orm') {
            return $this->getORM();
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Property is %s undefined in %s',
                $name,
                static::class
            )
        );
    }
}
