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
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Event\HydrateEvent;
use Windwalker\Database\Hydrator\HydratorInterface;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Hydrator\EntityHydrator;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Metadata\EntityMetadataCollection;
use Windwalker\ORM\Strategy\Selector;
use Windwalker\ORM\Test\Entity\User;

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

    public function from(mixed $tables, ?string $alias = null): Selector
    {
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

    public function extractEntity(object $entity): array
    {
        return $this->getEntityHydrator()->extract($entity);
    }

    public function getEntityMetadata(string|object $entity): EntityMetadata
    {
        return $this->getEntityMetadataCollection()->get($entity);
    }

    /**
     * findOne
     *
     * @param  string|object  $entity
     * @param  mixed          $conditions
     *
     * @return  object|null
     *
     * @template T
     * @psalm-param T $entity
     * @psalm-return T
     *
     * @throws \ReflectionException
     */
    public function findOne(string|object $entity, mixed $conditions): ?object
    {
        if (is_string($entity)) {
            $entity = $this->getAttributesResolver()->createObject($entity);
        }

        $metadata = $this->entityMetadataCollection->get($entity);

        return $this->from($metadata->getTableName())
            ->select('*')
            ->where(static::conditionsToWheres($metadata, $conditions))
            ->get($metadata->getClassName());
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
