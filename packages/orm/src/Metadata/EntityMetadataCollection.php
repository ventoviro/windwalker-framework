<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Metadata;

/**
 * The EntityMetadataCollection class.
 */
class EntityMetadataCollection
{
    /**
     * @var EntityMetadata[]
     */
    protected array $metadataList = [];

    /**
     * EntityMetadataCollection constructor.
     *
     * @param  EntityMetadata[]  $metadataItems
     */
    public function __construct(array $metadataItems = [])
    {
        $this->metadataList = $metadataItems;
    }

    public function get(string|object $entity): EntityMetadata
    {
        $class = is_object($entity) ? $entity::class : $entity;

        $class = strtolower(trim($class, '\\'));

        return $this->metadataList[$class] ??= new EntityMetadata($entity);
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
}
