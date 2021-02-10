<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Metadata;

use Windwalker\Attributes\AttributesResolver;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\Utilities\Cache\InstanceCacheTrait;

use function Windwalker\iterator_keys;

/**
 * The EntityMetadata class.
 */
class EntityMetadata
{
    use InstanceCacheTrait;

    protected string $className;

    protected ?string $tableName = null;

    /**
     * EntityMetadata constructor.
     *
     * @param  string|object  $entity
     */
    public function __construct(string|object $entity)
    {
        if (is_object($entity)) {
            $entity = $entity::class;
        }

        $this->className = $entity;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getTableName(): string
    {
        if ($this->tableName) {
            return $this->tableName;
        }

        $tableAttr = AttributesResolver::getFirstAttributeInstance($this->className, Table::class);

        if (!$tableAttr) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s has no table info.',
                    $this->className
                )
            );
        }

        return $this->tableName = $tableAttr->getName();
    }

    public function getMainKey(): ?string
    {
        $pks = [];

        foreach ($this->getKeysAttrs() as $name => [$prop, $pk, $col]) {
            /** @var PK $pk */
            /** @var Column $col */
            if ($pk->isPrimary()) {
                return $col->getName();
            }

            $pks[] = $name;
        }

        return $pks[0] ?? null;
    }

    /**
     * getKeys
     *
     * @return  string[]
     */
    public function getKeys(): array
    {
        return iterator_keys($this->getKeysAttrs());
    }

    /**
     * getKeysReflectors
     *
     * @return  \Generator
     */
    protected function getKeysAttrs(): \Generator
    {
        foreach ($this->getReflectProperties() as $prop) {
            show(
                AttributesResolver::getFirstAttributeInstance($prop, PK::class)
            );

            if (
                ($pk = AttributesResolver::getFirstAttributeInstance($prop, PK::class))
                && ($col = AttributesResolver::getFirstAttributeInstance($prop, Column::class))
            ) {
                show($pk, $col);
                yield $col->getName() => [$prop, $pk, $col];
            }
        }
    }

    /**
     * getProperties
     *
     * @return  array<int, \ReflectionProperty>
     */
    public function getReflectProperties(): array
    {
        return $this->getReflector()
            ->getProperties(
                \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE
            );
    }

    public function getReflector(): \ReflectionClass
    {
        return new \ReflectionClass($this->className);
    }
}
