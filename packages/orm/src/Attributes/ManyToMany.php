<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use ReflectionProperty;
use Windwalker\ORM\Relation\RelationManager;
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

/**
 * The ManyToMany class.
 */
#[Attribute]
class ManyToMany extends AbstractRelationAttribute
{
    /**
     * ManyToMany constructor.
     *
     * @param  string|null  $mapTable
     * @param  mixed        ...$mapColumns
     */
    public function __construct(?string $mapTable = null, ...$mapColumns)
    {
        parent::__construct($mapTable, ...$mapColumns);
    }

    protected function createRelation(RelationManager $rm, ReflectionProperty $prop): RelationConfigureInterface
    {
        return $rm->manyToMany($prop->getName())->targetTo($this->target, ...$this->columns);
    }
}
