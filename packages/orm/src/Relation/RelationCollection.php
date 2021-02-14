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
     * @var Relation[]
     */
    protected array $relations = [];

    public function getRelation(string $name): Relation
    {
        return $this->relations[$name] ??= new Relation();
    }

    public function setRelation(string $name, Relation $relation): static
    {
        $this->relations[$name] = $relation;

        return $this;
    }

    /**
     * @return Relation[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param  Relation[]  $relations
     *
     * @return  static  Return self to support chaining.
     */
    public function setRelations(array $relations): static
    {
        $this->relations = $relations;

        return $this;
    }
}
