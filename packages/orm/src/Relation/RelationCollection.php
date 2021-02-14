<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation;

/**
 * The RelationCollections class.
 */
class RelationCollection
{
    /**
     * @var RelationManager[]
     */
    protected array $relations = [];

    public function getRelation(string $name): RelationManager
    {
        return $this->relations[$name] ??= new RelationManager();
    }

    public function setRelation(string $name, RelationManager $relation): static
    {
        $this->relations[$name] = $relation;

        return $this;
    }

    /**
     * @return RelationManager[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param  RelationManager[]  $relations
     *
     * @return  static  Return self to support chaining.
     */
    public function setRelations(array $relations): static
    {
        $this->relations = $relations;

        return $this;
    }
}
