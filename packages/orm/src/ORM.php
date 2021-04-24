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
use Windwalker\Database\Hydrator\FieldHydratorInterface;
use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\CastNullable;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\EntitySetup;
use Windwalker\ORM\Attributes\ManyToMany;
use Windwalker\ORM\Attributes\ManyToOne;
use Windwalker\ORM\Attributes\Mapping;
use Windwalker\ORM\Attributes\NestedSet;
use Windwalker\ORM\Attributes\OneToMany;
use Windwalker\ORM\Attributes\OneToOne;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Event\AfterDeleteEvent;
use Windwalker\ORM\Event\AfterSaveEvent;
use Windwalker\ORM\Event\AfterUpdateWhereEvent;
use Windwalker\ORM\Event\BeforeDeleteEvent;
use Windwalker\ORM\Event\BeforeSaveEvent;
use Windwalker\ORM\Event\BeforeUpdateWhereEvent;
use Windwalker\ORM\Hydrator\EntityHydrator;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Metadata\EntityMetadataCollection;
use Windwalker\Query\Query;

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
 * @method  StatementInterface[]  deleteWhere(string $entityClass, mixed $conditions)
 * @method  iterable|object[]     flush(string $entityClass, iterable $items, mixed $conditions = [])
 * @method  StatementInterface[]  sync(string $entityClass, iterable $items, mixed $conditions = [], ?array $compareKeys = null)
 */
class ORM
{
    use AttributesAwareTrait;

    protected DatabaseAdapter $db;

    protected ?FieldHydratorInterface $hydrator = null;

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

        $this->init();
    }

    protected function init()
    {
        $ar = $this->getAttributesResolver();
        $ar->setOption('orm', $this);

        $ar->registerAttribute(AutoIncrement::class, \Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(Cast::class, \Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(CastNullable::class, \Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(Column::class, \Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(Mapping::class, \Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(PK::class, \Attribute::TARGET_PROPERTY);

        $ar->registerAttribute(OneToOne::class, \Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(OneToMany::class, \Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(ManyToOne::class, \Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(ManyToMany::class, \Attribute::TARGET_PROPERTY);

        $ar->registerAttribute(Table::class, \Attribute::TARGET_CLASS);
        $ar->registerAttribute(NestedSet::class, \Attribute::TARGET_CLASS);

        $ar->registerAttribute(EntitySetup::class, \Attribute::TARGET_METHOD);
        $ar->registerAttribute(BeforeSaveEvent::class, \Attribute::TARGET_METHOD);
        $ar->registerAttribute(AfterSaveEvent::class, \Attribute::TARGET_METHOD);
        $ar->registerAttribute(BeforeUpdateWhereEvent::class, \Attribute::TARGET_METHOD);
        $ar->registerAttribute(AfterUpdateWhereEvent::class, \Attribute::TARGET_METHOD);
        $ar->registerAttribute(BeforeDeleteEvent::class, \Attribute::TARGET_METHOD);
        $ar->registerAttribute(AfterDeleteEvent::class, \Attribute::TARGET_METHOD);
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

    public function from(mixed $tables, ?string $alias = null): SelectorQuery
    {
        if (is_string($tables) && class_exists($tables)) {
            return $this->mapper($tables)->select();
        }

        return $this->createSelectorQuery()->from($tables, $alias);
    }

    public function select(...$columns): SelectorQuery
    {
        return $this->createSelectorQuery()->select(...$columns);
    }

    protected function createSelectorQuery(): SelectorQuery
    {
        return new SelectorQuery($this);
    }

    public function insert(string $table, bool $incrementField = false): Query
    {
        if (is_string($table) && class_exists($table)) {
            return $this->mapper($table)->insert($incrementField);
        }

        return $this->createSelectorQuery()->insert($table, $incrementField);
    }

    public function update(string $table, ?string $alias = null): Query
    {
        if (is_string($table) && class_exists($table)) {
            return $this->mapper($table)->update($alias);
        }

        return $this->createSelectorQuery()->update($table, $alias);
    }

    public function delete(string $table, ?string $alias = null): Query
    {
        if (is_string($table) && class_exists($table)) {
            return $this->mapper($table)->delete($alias);
        }

        return $this->createSelectorQuery()->delete($table, $alias);
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

    public function extractField(array|object $entity, string $field): mixed
    {
        if (is_array($entity)) {
            return $entity[$field];
        }

        return $this->getEntityHydrator()->extractField($entity, $field);
    }

    public function getEntityClass(string|object $entity): string
    {
        if (is_object($entity)) {
            $entity = $entity::class;
        }

        return $entity;
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
     * @return FieldHydratorInterface
     * @throws \ReflectionException
     */
    public function getEntityHydrator(): FieldHydratorInterface
    {
        return $this->hydrator ??= $this->getAttributesResolver()
            ->createObject(
                EntityHydrator::class,
                hydrator: $this->getDb()->getHydrator(),
                orm: $this
            );
    }

    /**
     * @param  FieldHydratorInterface|null  $hydrator
     *
     * @return  static  Return self to support chaining.
     */
    public function setEntityHydrator(?FieldHydratorInterface $hydrator): static
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    public function countWith(Query|string $query): int
    {
        return $this->db->countWith($query);
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
