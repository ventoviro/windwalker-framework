<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Steategy;

use Windwalker\ORM\ORM;

/**
 * The AbstractRelationStrategy class.
 */
abstract class AbstractRelationStrategy
{
    protected $parent;

    /**
     * Property onUpdate.
     *
     * @var  string
     */
    protected string $onUpdate;

    /**
     * Property onDelete.
     *
     * @var  string
     */
    protected string $onDelete;

    protected string $field;

    protected array $fks = [];

    protected string $tableName = '';

    protected ORM $orm;

    protected bool $flush = false;
}
