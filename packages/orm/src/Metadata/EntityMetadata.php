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
use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\CastManager;
use Windwalker\ORM\ORM;
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

    protected CastManager $castManager;

    /**
     * @var ORM
     */
    protected ORM $orm;

    /**
     * @var array
     */
    protected array $attributeMaps = [];

    /**
     * EntityMetadata constructor.
     *
     * @param  string|object  $entity
     * @param  ORM            $orm
     */
    public function __construct(string|object $entity, ORM $orm)
    {
        if (is_object($entity)) {
            $entity = $entity::class;
        }

        $this->orm         = $orm;
        $this->className   = $entity;
        $this->castManager = new CastManager();

        $this->setup();
    }

    public static function isEntity(string|object $object): bool
    {
        return (new \ReflectionClass($object))->getAttributes(Table::class) !== [];
    }

    public function setup(): void
    {
        foreach ($this->getReflectProperties() as $prop) {
            $attributes = $prop->getAttributes();

            foreach ($attributes as $attribute) {
                if (!$attribute->isRepeated()) {
                    $this->attributeMaps[$attribute::class]
                }
            }

            $casts = $prop->getAttributes(Cast::class);

            if ($casts === []) {
                continue;
            }

            $column = AttributesResolver::getFirstAttributeInstance(
                $prop,
                Column::class
            );

            $colName = $column ? $column->getName() : $prop->getName();

            foreach ($casts as $castAttr) {
                /** @var Cast $cast */
                $cast = $castAttr->newInstance();
                $this->cast(
                    $colName,
                    $cast->getCast(),
                    $cast->getExtract(),
                    $cast->getStrategy()
                );
            }
        }

        $ref = $this->getReflector();

        $methods = $ref->getMethods(
            \ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PUBLIC
        );

        foreach ($methods as $method) {

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

        foreach ($this->getKeysAttrs() as $name => $pk) {
            $col = $pk->getColumn();

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

    public function getAutoIncrementColumn(): ?Column
    {
        foreach ($this->getKeysAttrs() as $keyAttr) {
            $column = $keyAttr->getColumn();
            $prop   = $column->getProperty();

            if ($prop->getAttributes(AutoIncrement::class)) {
                return $column;
            }
        }

        return null;
    }

    /**
     * getKeysReflectors
     *
     * @return  \Generator|PK[]
     */
    protected function getKeysAttrs(): \Generator
    {
        foreach ($this->getColumnAttrs() as $key => $column) {
            $prop = $column->getProperty();

            if ($pk = AttributesResolver::getFirstAttributeInstance($prop, PK::class)) {
                yield $key => $pk->setColumn($column);
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

    public function getProperty(string $name): \ReflectionProperty
    {
        return $this->getReflector()->getProperty($name);
    }

    /**
     * getColumns
     *
     * @return \Generator|Column[]
     */
    public function getColumnAttrs(): \Generator
    {
        foreach ($this->getReflectProperties() as $prop) {
            if ($col = AttributesResolver::getFirstAttribute($prop, Column::class)) {
                /** @var Column $column */
                $column = $col->newInstance();
                $column->setProperty($prop);

                yield $column->getName() => $column;
            }
        }
    }

    public function getColumn(string $name): ?Column
    {
        return iterator_to_array($this->getColumnAttrs())[$name] ?? null;
    }

    public function getReflector(): \ReflectionClass
    {
        return new \ReflectionClass($this->className);
    }

    public function cast(
        string $field,
        mixed $cast,
        mixed $extract = null,
        int $strategy = Cast::CONSTRUCTOR
    ): static {
        $this->getCastManager()->addCast(
            $field,
            $cast,
            $extract,
            $strategy
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
