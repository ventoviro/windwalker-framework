<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Strategy;

use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Relation\RelationCollection;
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\ORM\Strategy\Selector;
use Windwalker\Query\Clause\JoinClause;
use Windwalker\Utilities\Assert\TypeAssert;

/**
 * The ManyToMany class.
 */
class ManyToMany extends AbstractRelation
{
    /**
     * @inheritDoc
     */
    public function __construct(
        EntityMetadata $metadata,
        string $propName,
        protected ?string $intermediate = null,
        protected array $mapFks = [],
        ?string $targetTable = null,
        array $fks = [],
        string $onUpdate = Action::NO_ACTION,
        string $onDelete = Action::NO_ACTION,
        array $options = []
    ) {
        parent::__construct(
            $metadata,
            $propName,
            $targetTable,
            $fks,
            $onUpdate,
            $onDelete,
            $options
        );
    }

    /**
     * @inheritDoc
     */
    public function load(array $data, object $entity): array
    {
        $getter = fn() => $this->createCollection($data);

        RelationProxies::set($entity, $this->getPropName(), $getter);

        return $data;
    }

    protected function createCollection(array $data): RelationCollection
    {
        return new RelationCollection(
            $this->targetTable,
            $this->createCollectionQuery($data)
        );
    }

    protected function createCollectionQuery(array $data): Selector
    {
        $foreignMetadata = $this->getForeignMetadata();
        $intermediateMetadata = $this->getIntermediateMetadata();

        return $this->getORM()
            ->from($foreignMetadata->getTableName(), $foreignMetadata->getTableAlias())
            ->leftJoin(
                $intermediateMetadata->getTableName(),
                $intermediateMetadata->getTableAlias(),
                function (JoinClause $joinClause) use ($foreignMetadata, $intermediateMetadata) {
                    foreach ($this->getForeignKeys() as $mapKey => $foreignKey) {
                        $joinClause->on(
                            $foreignMetadata->getTableAlias() . '.' . $foreignKey,
                            $intermediateMetadata->getTableAlias() . '.' . $mapKey
                        );
                    }
                }
            )
            ->where(
                $this->createLoadConditions(
                    $data,
                    $intermediateMetadata->getTableAlias()
                )
            )
            ->groupByJoins();
    }

    public function createLoadConditions(array $data, ?string $alias = null): array
    {
        $conditions = [];

        foreach ($this->mapFks as $field => $foreign) {
            if ($alias) {
                $foreign = $alias . '.' . $foreign;
            }

            $conditions[$foreign] = $data[$field];
        }

        return $conditions;
    }

    /**
     * @inheritDoc
     */
    public function save(array $data, object $entity, ?array $oldData = null): void
    {
    }

    /**
     * @inheritDoc
     */
    public function delete(array $data, object $entity): void
    {
    }

    public function getIntermediateMetadata(): EntityMetadata
    {
        return $this->getORM()->getEntityMetadata($this->intermediate);
    }

    /**
     * @return array
     */
    public function getMapForeignKeys(): array
    {
        return $this->mapFks;
    }

    /**
     * @return string|null
     */
    public function getIntermediate(): ?string
    {
        return $this->intermediate;
    }

    /**
     * @param  string|null   $intermediate
     * @param  array|string  $ownerKey
     * @param  string|null   $mapKey
     *
     * @return  static  Return self to support chaining.
     */
    public function through(?string $intermediate, array|string $ownerKey, ?string $mapKey = null): static
    {
        $fks = $ownerKey;

        if (is_string($fks)) {
            TypeAssert::assert(
                $mapKey !== null,
                '{caller} argument #2 and #3, should have a foreign key pair, the foreign key is {value}.',
                $mapKey
            );

            $fks = [$fks => $mapKey];
        } else {
            $fks = $ownerKey;
        }

        $this->intermediate = $intermediate;

        $this->setMapForeignKeys($fks);

        return $this;
    }

    /**
     * @param  array  $mapFks
     *
     * @return  static  Return self to support chaining.
     */
    public function setMapForeignKeys(array $mapFks): static
    {
        $this->mapFks = $mapFks;

        return $this;
    }
}
