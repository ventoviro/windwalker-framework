<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Metadata;

use Windwalker\ORM\Attributes\{Column, PK, Table, Watch};
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\ORM\Cast\CastManager;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Event\AfterSaveEvent;
use Windwalker\ORM\Event\AfterUpdateWhereEvent;
use Windwalker\ORM\Event\BeforeSaveEvent;
use Windwalker\ORM\Event\BeforeUpdateWhereEvent;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Relation\RelationManager;
use Windwalker\Utilities\Cache\RuntimeCacheTrait;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The EntityMetadata class.
 */
class EntityMetadata implements EventAwareInterface
{
    use EventAwareTrait;
    use RuntimeCacheTrait;
    use OptionAccessTrait;

    protected string $className;

    protected ?string $tableName = null;

    protected ?string $tableAlias = null;

    protected string $mapperClass = EntityMapper::class;

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

        $this->orm             = $orm;
        $this->className       = $entity;
        $this->castManager     = new CastManager();
        $this->relationManager = new RelationManager($this);

        $this->setup();
    }

    public static function isEntity(string|object $object): bool
    {
        $class = new \ReflectionClass($object);

        return $class->getAttributes(Table::class, \ReflectionAttribute::IS_INSTANCEOF) !== [];
    }

    public function setup(): static
    {
        $resolver = $this->getORM()->getAttributesResolver();
        $resolver->setOption('metadata', $this);

        $ref = $this->getReflector();

        $resolver->resolveObjectDecorate($ref);

        if (!$this->tableName) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s has no table info.',
                    $this->className
                )
            );
        }

        $resolver->resolveObjectMembers($ref);

        $resolver->setOption('metadata', null);

        return $this;
    }

    public function addAttributeMap(string $attrName, \ReflectionProperty|\ReflectionMethod $ref): void
    {
        $type = $ref instanceof \ReflectionProperty ? 'props' : 'methods';

        $this->attributeMaps[$attrName][$type][$ref->getName()] = $ref;
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

        $tableAttr = $this->getReflector()
            ->getAttributes(Table::class, \ReflectionAttribute::IS_INSTANCEOF)[0]?->newInstance();

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
        int $options = 0
    ): static {
        $this->getCastManager()->addCast(
            $field,
            $cast,
            $extract,
            $options
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

    public function getEntityMapper(?string $mapperClass = null): EntityMapper
    {
        $args = [
            $this,
            $orm = $this->getORM(),
        ];

        $mapperClass ??= $this->getMapperClass();

        if ($mapperClass === EntityMapper::class) {
            $mapper = new EntityMapper(...$args);
        } else {
            $mapper = $orm->getAttributesResolver()->createObject(
                $mapperClass,
                ...$args
            );
        }

        $mapper->addEventDealer($this);

        return $mapper;
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

    /**
     * @return string|null
     */
    public function getTableAlias(): ?string
    {
        return $this->tableAlias;
    }

    /**
     * @return string
     */
    public function getMapperClass(): string
    {
        return $this->mapperClass;
    }

    public function watch(string $column, callable $method, int $options = 0): \Closure
    {
        $unwatches = [];

        if ($options & Watch::BEFORE_SAVE) {
            $this->on(
                BeforeSaveEvent::class,
                $unwatches[BeforeSaveEvent::class] = function (BeforeSaveEvent $event) use ($column, $options, $method) {
                    if (!($options & Watch::ON_CREATE) && $event->getType() === BeforeSaveEvent::TYPE_CREATE) {
                        return;
                    }

                    $val    = $event->getData()[$column] ?? null;
                    $oldVal = $event->getOldData()[$column] ?? null;

                    if ($val !== $oldVal) {
                        $watchEvent = Watch::createWatchEvent($event, $val, $oldVal);

                        $this->getORM()->getAttributesResolver()
                            ->call(
                                $method,
                                [
                                    $watchEvent::class => $watchEvent,
                                    'event' => $watchEvent,
                                ]
                            );
                    }
                }
            );
            $this->on(
                BeforeUpdateWhereEvent::class,
                $unwatches[BeforeUpdateWhereEvent::class] = function (BeforeUpdateWhereEvent $event) use ($column, $method) {
                    $val = $event->getData()[$column] ?? null;

                    $watchEvent = Watch::createWatchEvent($event, $val);

                    $this->getORM()->getAttributesResolver()
                        ->call(
                            $method,
                            [
                                $watchEvent::class => $watchEvent,
                                'event' => $watchEvent,
                            ]
                        );
                }
            );
        } else {
            $this->on(
                AfterSaveEvent::class,
                $unwatches[AfterSaveEvent::class] = function (AfterSaveEvent $event) use ($column, $options, $method) {
                    if (!($options & Watch::ON_CREATE) && $event->getType() === AfterSaveEvent::TYPE_CREATE) {
                        return;
                    }

                    $val    = $event->getData()[$column] ?? null;
                    $oldVal = $event->getOldData()[$column] ?? null;

                    if ($val !== $oldVal) {
                        $watchEvent = Watch::createWatchEvent($event, $val, $oldVal);

                        $this->getORM()->getAttributesResolver()
                            ->call(
                                $method,
                                [
                                    $watchEvent::class => $watchEvent,
                                    'event' => $watchEvent,
                                ]
                            );
                    }
                }
            );
            $this->on(
                AfterUpdateWhereEvent::class,
                $unwatches[AfterUpdateWhereEvent::class] = function (AfterUpdateWhereEvent $event) use ($column, $method) {
                    $val = $event->getData()[$column] ?? null;

                    $watchEvent = Watch::createWatchEvent($event, $val);

                    $this->getORM()->getAttributesResolver()
                        ->call(
                            $method,
                            [
                                $watchEvent::class => $watchEvent,
                                'event' => $watchEvent,
                            ]
                        );
                }
            );
        }

        return function () use ($unwatches) {
            foreach ($unwatches as $event => $listener) {
                $this->getEventDispatcher()->off($event, $listener);
            }
        };
    }
}
