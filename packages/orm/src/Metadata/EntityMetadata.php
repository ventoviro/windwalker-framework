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
use Windwalker\Database\Hydrator\HydratorAwareInterface;
use Windwalker\Database\Hydrator\HydratorInterface;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\CastManager;
use Windwalker\ORM\ORM;
use Windwalker\Utilities\Cache\InstanceCacheTrait;
use Windwalker\Utilities\Classes\ObjectBuilder;

use function Windwalker\iterator_keys;

/**
 * The EntityMetadata class.
 */
class EntityMetadata
{
    use InstanceCacheTrait;

    protected string $className;

    protected ?string $tableName = null;

    protected CastManager $castManager;

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
        $this->castManager = new CastManager();

        $this->setup();
    }

    public function setup(): void
    {
        /** @var \ReflectionAttribute $refColumn */
        /** @var \ReflectionProperty $prop */
        foreach ($this->getReflectColumns() as [$prop, $refColumn]) {
            /** @var Column $column */
            $column = $refColumn->newInstance();
            $casts   = $prop->getAttributes(Cast::class);

            if ($casts === []) {
                continue;
            }

            foreach ($casts as $cast) {
                $cast = $cast->newInstance();
                $this->cast(
                    $column->getName(),
                    $cast->getCast(),
                    $cast->getExtract(),
                    $cast->getHydrateStrategy()
                );
            }
        }

        if (is_subclass_of($this->className, EntitySetupInterface::class)) {
            $this->className::setup($this);
        }
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
        foreach ($this->getReflectColumns() as [$prop, $refColumn]) {
            /** @var \ReflectionProperty $prop */
            /** @var Column $column */
            $column = $refColumn->newInstance();

            if ($pk = AttributesResolver::getFirstAttributeInstance($prop, PK::class)) {
                yield $column->getName() => [$prop, $pk, $column];
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

    /**
     * getColumns
     *
     * @return \Generator<int, array<\Reflector>>
     */
    public function getReflectColumns(): \Generator
    {
        foreach ($this->getReflectProperties() as $key => $prop) {
            if ($col = AttributesResolver::getFirstAttribute($prop, Column::class)) {
                yield $key => [$prop, $col];
            }
        }
    }

    public function getReflector(): \ReflectionClass
    {
        return new \ReflectionClass($this->className);
    }

    public function cast(
        string $field,
        mixed $cast,
        mixed $extract = null,
        int $hydrateStrategy = Cast::CONSTRUCTOR
    ): static {
        $this->getCastManager()->addCast(
            $field,
            $cast,
            $extract,
            $hydrateStrategy
        );

        return $this;
    }

    /**
     * @return CastManager
     */
    public function getCastManager(): CastManager
    {
        return $this->castManager;
    }

    /**
     * @param  CastManager  $castManager
     *
     * @return  static  Return self to support chaining.
     */
    public function setCastManager(CastManager $castManager): static
    {
        $this->castManager = $castManager;

        return $this;
    }
}
