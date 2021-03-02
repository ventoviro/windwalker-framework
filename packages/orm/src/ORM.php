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
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Hydrator\HydratorInterface;
use Windwalker\ORM\Hydrator\EntityHydrator;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Metadata\EntityMetadataCollection;
use Windwalker\ORM\Strategy\Selector;

/**
 * The ORM class.
 *
 * @method  object|null   findOne(string $entityClass, mixed $conditions)
 * @method  \Generator    findList(string $entityClass, mixed $conditions = [])
 * @method  string|null   findResult(string $entityClass, mixed $conditions)
 * @method  object|object[]  createOne(string $entityClass, array|object $item = [])
 * @method  iterable      createMultiple(string $entityClass, iterable $items)
 * @method  StatementInterface|null  updateOne(string $entityClass, array|object $item = [], array|string $condFields = null, bool $updateNulls = false)
 * @method  object[]      updateMultiple(string $entityClass, iterable $items, array|string $condFields = null, $updateNulls = false)
 * @method  StatementInterface  updateWhere(string $entityClass, array|object $data, mixed $conditions = null)
 * @method  StatementInterface[]  updateBatch(string $entityClass, array|object $data, mixed $conditions = null, $updateNulls = false)
 * @method  iterable|object[] saveMultiple(string $entityClass, iterable $items, string|array $condFields = null, bool $updateNulls = false)
 * @method  object        saveOne(string $entityClass, array|object $item, array|string $condFields = null, bool $updateNulls = false)
 * @method  object        findOneOrCreate(string $entityClass, mixed $conditions, mixed $initData = null, bool $mergeConditions = true)
 * @method  object        updateOneOrCreate(string $entityClass, array|object $item, mixed $initData = null, ?array $condFields = null, bool $updateNulls = false)
 * @method  StatementInterface[]  delete(string $entityClass, mixed $conditions)
 * @method  iterable|object[]     flush(string $entityClass, iterable $items, mixed $conditions = [])
 * @method  StatementInterface[]  sync(string $entityClass, iterable $items, mixed $conditions = [], ?array $compareKeys = null)
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

        $this->entityMetadataCollection = new EntityMetadataCollection($this);
    }

    /**
     * entity
     *
     * @param  string       $entityClass
     * @param  string|null  $mapperClass
     *
     * @return  EntityMapper
     * @throws \ReflectionException
     */
    public function mapper(string $entityClass, ?string $mapperClass = null): EntityMapper
    {
        return $this->getEntityMetadata($entityClass)->getMapper($mapperClass);
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

    public function __call(string $name, array $args = []): mixed
    {
        if (method_exists(EntityMapper::class, $name)) {
            $entity = array_shift($args);

            return $this->mapper($entity)->$name(...$args);
        }

        throw new \BadMethodCallException(
            sprintf(
                'Call to undefined method %s::%s()',
                static::class,
                $name
            )
        );
    }
}
