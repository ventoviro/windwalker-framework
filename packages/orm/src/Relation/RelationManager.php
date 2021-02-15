<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation;

use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Relation\Steategy\AbstractRelation;
use Windwalker\ORM\Relation\Steategy\ManyToOne;
use Windwalker\ORM\Relation\Steategy\OneToMany;
use Windwalker\ORM\Relation\Steategy\OneToOne;
use Windwalker\ORM\Relation\Steategy\RelationStrategyInterface;

/**
 * The Relation class.
 */
class RelationManager implements RelationStrategyInterface
{
    /**
     * @var AbstractRelation[]
     */
    protected array $relations = [];

    /**
     * RelationManager constructor.
     *
     * @param  EntityMetadata  $metadata
     */
    public function __construct(protected EntityMetadata $metadata)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function load(array $data, object $entity): array
    {
        foreach ($this->getRelations() as $relation) {
            $data = $relation->load($data, $entity);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function save(array $data, object $entity): void
    {
        foreach ($this->getRelations() as $relation) {
            $relation->save($data, $entity);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(array $data, object $entity): void
    {
        foreach ($this->getRelations() as $relation) {
            $relation->delete($data, $entity);
        }
    }

    public function oneToOne(
        string $field,
        ?string $targetTable = null,
        array|string $fks = [],
        string $onUpdate = Action::NO_ACTION,
        string $onDelete = Action::NO_ACTION,
        array $options = [],
    ) {
        $rel = new OneToOne(
            $this->getMetadata(),
            $field,
            $targetTable,
            $fks,
            $onUpdate,
            $onDelete,
            $options
        );

        return $this->relations[$field] = $rel;
    }

    public function oneToMany(
        string $field,
        ?string $targetTable = null,
        array|string $fks = [],
        string $onUpdate = Action::NO_ACTION,
        string $onDelete = Action::NO_ACTION,
        array $options = [],
    ) {
        $rel = new OneToMany(
            $this->getMetadata(),
            $field,
            $targetTable,
            $fks,
            $onUpdate,
            $onDelete,
            $options
        );

        return $this->relations[$field] = $rel;
    }

    public function manyToOne(
        string $field,
        ?string $targetTable = null,
        array|string $fks = [],
        string $onUpdate = Action::NO_ACTION,
        string $onDelete = Action::NO_ACTION,
        array $options = [],
    ) {
        $rel = new ManyToOne(
            $this->getMetadata(),
            $field,
            $targetTable,
            $fks,
            $onUpdate,
            $onDelete,
            $options
        );

        return $this->relations[$field] = $rel;
    }

    /**
     * @return EntityMetadata
     */
    public function getMetadata(): EntityMetadata
    {
        return $this->metadata;
    }

    /**
     * @return AbstractRelation[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    public function getRelation(string $propName): ?AbstractRelation
    {
        return $this->relations[$propName] ?? null;
    }

    public function addRelation(string $field, RelationStrategyInterface $relation): RelationStrategyInterface
    {
        return $this->relations[$field] = $relation;
    }
}
