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
 * The TargetTo class.
 */
#[Attribute]
class TargetTo implements RelationConfigureAttributeInterface
{
    protected array $columns;

    /**
     * OneToOne constructor.
     *
     * @param  string  $target
     * @param  mixed   ...$columns
     */
    public function __construct(
        protected string $target,
        ...$columns
    ) {
        $this->columns = $columns;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(RelationConfigureInterface $relation): void
    {
        $relation->targetTo($this->target, $this->columns);
    }
}
