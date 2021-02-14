<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Metadata;

use Windwalker\ORM\ORM;

/**
 * The EntityMetadataCollection class.
 */
class EntityMetadataCollection
{
    /**
     * @var EntityMetadata[]
     */
    protected array $metadataList = [];

    protected ORM $orm;

    /**
     * EntityMetadataCollection constructor.
     *
     * @param  EntityMetadata[]  $metadataItems
     * @param  ORM               $orm
     */
    public function __construct(ORM $orm, array $metadataItems = [])
    {
        $this->metadataList = $metadataItems;
        $this->orm = $orm;
    }

    public function get(string|object $entity): EntityMetadata
    {
        $class = is_object($entity) ? $entity::class : $entity;

        $class = strtolower(trim($class, '\\'));

        return $this->metadataList[$class] ??= new EntityMetadata($entity, $this->getORM());
    }

    public function set(EntityMetadata $metadata): static
    {
        $class = $metadata->getClassName();

        $class = strtolower(trim($class, '\\'));

        $this->metadataList[$class] = $metadata;

        return $this;
    }

    public function remove(object|string $classOrMetadata): static
    {
        if ($classOrMetadata instanceof EntityMetadata) {
            $class = $classOrMetadata->getClassName();
        } elseif (is_object($classOrMetadata)) {
            $class = $classOrMetadata::class;
        } else {
            $class = $classOrMetadata;
        }

        $class = strtolower(trim($class, '\\'));

        unset($this->metadataList[$class]);

        return $this;
    }

    /**
     * @return EntityMetadata[]
     */
    public function getMetadataList(): array
    {
        return $this->metadataList;
    }

    /**
     * @param  EntityMetadata[]  $metadataList
     *
     * @return  static  Return self to support chaining.
     */
    public function setMetadataList(array $metadataList): static
    {
        $this->metadataList = $metadataList;

        return $this;
    }

    /**
     * @return ORM
     */
    public function getORM(): ORM
    {
        return $this->orm;
    }

    /**
     * @param  ORM  $orm
     *
     * @return  static  Return self to support chaining.
     */
    public function setORM(ORM $orm): static
    {
        $this->orm = $orm;

        return $this;
    }
}
