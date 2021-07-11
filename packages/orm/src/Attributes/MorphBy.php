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
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

/**
 * The MorphBy class.
 */
#[Attribute]
class MorphBy implements RelationConfigureAttributeInterface
{
    protected array $columns;

    /**
     * MorphBy constructor.
     *
     * @param  mixed  ...$columns
     */
    public function __construct(...$columns)
    {
        $this->columns = $columns;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(RelationConfigureInterface $relation): void
    {
        $relation->morphBy(...$this->columns);
    }
}
