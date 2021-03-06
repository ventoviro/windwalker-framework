<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\ORM\Relation\Strategy\ManyToMany;
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

/**
 * The MapMorphBy class.
 */
#[\Attribute]
class MapMorphBy extends MorphBy
{
    /**
     * @inheritDoc
     */
    public function __invoke(RelationConfigureInterface $relation): void
    {
        if (!$relation instanceof ManyToMany) {
            throw new \LogicException(
                sprintf(
                    '%s should use for %s',
                    static::class,
                    ManyToMany::class
                )
            );
        }

        $relation->mapMorphBy($this->columns);
    }
}
