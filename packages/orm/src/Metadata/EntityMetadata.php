<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Metadata;

use Windwalker\ORM\Attributes\{AutoIncrement, Cast, Column, EntitySetup, PK, Table};
use Windwalker\ORM\Cast\CastManager;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Relation\RelationManager;
use Windwalker\Utilities\Cache\RuntimeCacheTrait;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The EntityMetadata class.
 */
class EntityMetadata
{
    use RuntimeCacheTrait;

    protected string $className;

    protected ?string $tableName = null;

    protected ?Column $aiColumn = null;

    /**
     * @var PK[]
     */
    protected array $keys = [];

    /**
     * @var \ReflectionProperty[]
     */
    protected ?array $properties = null;

    protected array $propertyColumns = [];

    /**
     * @var \ReflectionMethod[]
     */
    protected ?array $methods = null;

    /**
     * @var Column[]
     */
    protected array $columns = [];

    /**
     * @var array
     */
    protected array $attributeMaps = [];

    protected CastManager $castManager;

    protected RelationManager $relationManager;

    /**
     * @var ORM
     */
    protected ORM $orm;

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
        $this->relationManager = new RelationManager($this);

        $this->setup();
    }

    public static function isEntity(string|object $object): bool
    {
        $class = new \ReflectionClass($object);

        return $class->getAttributes(Table::class) !== [];
    }

    public function setup(): static
    {
        // Loop all properties
        foreach ($this->getProperties() as $prop) {
            $attributes  = $prop->getAttributes();
            $singleAttrs = [];
            $column      = null;

            foreach ($attributes as $attribute) {
                if (!$attribute->isRepeated()) {
                    $this->attributeMaps[$attribute->getName()]['props'][$prop->getName()] = $prop;
                    $singleAttrs[$attribute->getName()]                                    = $attribute;
                }
            }

            if ($singleAttrs[Column::class] ?? null) {
                /** @var Column $column */
                $column = $singleAttrs[Column::class]->newInstance();
                $column->setProperty($prop);

                $this->columns[$column->getName()] = $column;
                $this->propertyColumns[$prop->getName()] = $column;
            }

            if ($singleAttrs[PK::class] ?? null) {
                /** @var PK $pk */
                $pk = $singleAttrs[PK::class]->newInstance();

                if ($column === null) {
                    throw new \LogicException(
                        sprintf(
                            '%s set on a property without %s',
                            PK::class,
                            Column::class
                        )
                    );
                }

                $this->keys[$column->getName()] = $pk->setColumn($column);
            }

            if ($singleAttrs[AutoIncrement::class] ?? null) {
                $this->aiColumn = $column;
            }

            // Register casts
            $casts = $prop->getAttributes(Cast::class);

            if (!$casts === []) {
                continue;
            }

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

        // Loop all methods
        foreach ($this->getMethods() as $method) {
            $attributes = $method->getAttributes();

            foreach ($attributes as $attribute) {
                // If is setup method, call it.
                if ($attribute->getName() === EntitySetup::class) {
                    $this->getORM()
                        ->getAttributesResolver()
                        ->call(
                            $method->getClosure(),
                            [
                                'metadata' => $this,
                                static::class => $this,
                            ]
                        );
                }

                // Cache method attributes
                if (!$attribute->isRepeated()) {
                    $this->attributeMaps[$attribute->getName()]['methods'][$method->getName()] = $method;
                }
            }
        }

        return $this;
    }

    /**
     * getMethodsOfAttribute
     *
     * @param  string  $attributeClass
     *
     * @return  \ReflectionMethod[]
     */
    public function getMethodsOfAttribute(string $attributeClass): array
    {
        return $this->attributeMaps[$attributeClass]['methods'] ?? [];
    }

    /**
     * getPropertiesOfAttribute
     *
     * @param  string  $attributeClass
     *
     * @return  \ReflectionProperty[]
     */
    public function getPropertiesOfAttribute(string $attributeClass): array
    {
        return $this->attributeMaps[$attributeClass]['props'] ?? [];
    }

    public function getColumnByPropertyName(string $propName): ?Column
    {
        return $this->propertyColumns[$propName] ?? null;
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

        $tableAttr = $this->getReflector()->getAttributes(Table::class)[0]?->newInstance();

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

    public function getMainKey(): ?string
    {
        $pks = [];

        foreach ($this->getKeysAttrs() as $name => $pk) {
            if ($pk->isPrimary()) {
                return $pk->getColumn()->getName();
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
        return array_keys($this->getKeysAttrs());
    }

    public function getAutoIncrementColumn(): ?Column
    {
        return $this->aiColumn;
    }

    /**
     * getKeysReflectors
     *
     * @return  PK[]
     */
    protected function getKeysAttrs(): array
    {
        return $this->keys;
    }

    /**
     * getMethods
     *
     * @return  array<int, \ReflectionMethod>
     *
     * @throws \ReflectionException
     */
    public function getMethods(): array
    {
        return $this->methods ??= ReflectAccessor::getReflectMethods(
            $this->className,
            \ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PUBLIC
        );
    }

    public function getMethod(string $name): ?\ReflectionMethod
    {
        return $this->getMethods()[$name] ?? null;
    }

    /**
     * getProperties
     *
     * @return  array<int, \ReflectionProperty>
     * @throws \ReflectionException
     */
    public function getProperties(): array
    {
        return $this->properties ??= ReflectAccessor::getReflectProperties(
            $this->className,
            \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE
        );
    }

    public function getProperty(string $name): ?\ReflectionProperty
    {
        return $this->getProperties()[$name] ?? null;
    }

    /**
     * getColumns
     *
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getColumn(string $name): ?Column
    {
        return $this->getColumns()[$name] ?? null;
    }

    /**
     * ReflectionClass creation is very fast that no need to cache it.
     *
     * @return  \ReflectionClass
     *
     * @throws \ReflectionException
     */
    public function getReflector(): \ReflectionClass
    {
        return new \ReflectionClass($this->className);
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

    /**
     * @return RelationManager
     */
    public function getRelationManager(): RelationManager
    {
        return $this->relationManager;
    }

    /**
     * @param  RelationManager  $relationManager
     *
     * @return  static  Return self to support chaining.
     */
    public function setRelationManager(RelationManager $relationManager): static
    {
        $this->relationManager = $relationManager;

        return $this;
    }
}
