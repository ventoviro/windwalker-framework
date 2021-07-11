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
use LogicException;
use Windwalker\ORM\Relation\Strategy\ManyToMany;
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

/**
 * The MapBy class.
 */
#[Attribute]
class MapBy implements RelationConfigureAttributeInterface
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
        if (!$relation instanceof ManyToMany) {
            throw new LogicException(
                sprintf(
                    '%s should use for %s',
                    static::class,
                    ManyToMany::class
                )
            );
        }

        $relation->mapBy($this->target, $this->columns);
    }
}
