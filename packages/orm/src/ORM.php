<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM;

use Windwalker\Attributes\AttributesAwareTrait;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Hydrator\HydratorInterface;
use Windwalker\ORM\Hydrator\EntityHydrator;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Metadata\EntityMetadataCollection;
use Windwalker\ORM\Strategy\Selector;

/**
 * The ORM class.
 */
class ORM
{
    use AttributesAwareTrait;

    protected DatabaseAdapter $db;

    protected ?HydratorInterface $hydrator = null;

    protected EntityMetadataCollection $entityMetadataCollection;

    /**
     * ORM constructor.
     *
     * @param  DatabaseAdapter  $db
     */
    public function __construct(DatabaseAdapter $db)
    {
        $this->db = $db;

        $this->entityMetadataCollection = new EntityMetadataCollection();
    }

    /**
     * entity
     *
     * @param  string  $entityClass
     *
     * @return  EntityMapper
     */
    public function mapper(string $entityClass): EntityMapper
    {
        return new EntityMapper(
            $this->entityMetadataCollection->get($entityClass),
            $this
        );
    }

    public function from(mixed $tables, ?string $alias = null): Selector
    {
        if (is_string($tables) && class_exists($tables)) {
            return $this->mapper($tables)->select();
        }

        return $this->createSelectorQuery()->from($tables, $alias);
    }

    public function select(...$columns): Selector
    {
        return $this->createSelectorQuery()->select(...$columns);
    }

    protected function createSelectorQuery(): Selector
    {
        return new Selector($this);
    }

    /**
     * hydrateEntity
     *
     * @param  array   $data
     * @param  object  $entity
     *
     * @return  object
     */
    public function hydrateEntity(array $data, object $entity): object
    {
        return $this->getEntityHydrator()->hydrate($data, $entity);
    }

    public function extractEntity(array|object $entity): array
    {
        if (is_array($entity)) {
            return $entity;
        }

        return $this->getEntityHydrator()->extract($entity);
    }

    public function getEntityMetadata(string|object $entity): EntityMetadata
    {
        return $this->getEntityMetadataCollection()->get($entity);
    }

    /**
     * findOne
     *
     * @param  string  $entity
     * @param  mixed   $conditions
     *
     * @return  object|null
     *
     * @throws \ReflectionException
     */
    public function findOne(string $entity, mixed $conditions): ?object
    {
        return $this->mapper($entity)->findOne($conditions);
    }

    public function createOne(string|object $entity, array|object $data = []): array|object
    {
        return $this->mapper($entity)->createOne($data);
    }

    public function updateOne(
        string|object $entity,
        array|object $data = [],
    ): array|object {
        return $this->mapper($entity)->updateOne($data);
    }

    public function deleteOne(
        string|object $entity,
        array|object $data = [],
    ): array|object {
        return $this->mapper($entity)->updateOne($data);
    }

    public static function conditionsToWheres(EntityMetadata $metadata, mixed $conditions): array
    {
        if (!is_array($conditions)) {
            $key = $metadata->getMainKey();

            if ($key) {
                $conditions = [$key => $conditions];
            } else {
                throw new \LogicException(
                    sprintf(
                        'Conditions cannot be scalars since %s has no keys',
                        $metadata->getClassName()
                    )
                );
            }
        }

        return $conditions;
    }

    /**
     * @return DatabaseAdapter
     */
    public function getDb(): DatabaseAdapter
    {
        return $this->db;
    }

    /**
     * @param  DatabaseAdapter  $db
     *
     * @return  static  Return self to support chaining.
     */
    public function setDb(DatabaseAdapter $db): static
    {
        $this->db = $db;

        return $this;
    }

    /**
     * @return EntityMetadataCollection
     */
    public function getEntityMetadataCollection(): EntityMetadataCollection
    {
        return $this->entityMetadataCollection;
    }

    /**
     * @param  EntityMetadataCollection  $entityMetadataCollection
     *
     * @return  static  Return self to support chaining.
     */
    public function setEntityMetadataCollection(EntityMetadataCollection $entityMetadataCollection): static
    {
        $this->entityMetadataCollection = $entityMetadataCollection;

        return $this;
    }

    /**
     * @return HydratorInterface
     */
    public function getEntityHydrator(): HydratorInterface
    {
        return $this->hydrator ??= $this->getAttributesResolver()
            ->createObject(
                EntityHydrator::class,
                hydrator: $this->getDb()->getHydrator(),
                orm: $this
            );
    }

    /**
     * @param  HydratorInterface|null  $hydrator
     *
     * @return  static  Return self to support chaining.
     */
    public function setEntityHydrator(?HydratorInterface $hydrator): static
    {
        $this->hydrator = $hydrator;

        return $this;
    }
}
