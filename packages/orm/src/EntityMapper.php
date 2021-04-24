<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM;

use Windwalker\Attributes\AttributesAccessor;
use Windwalker\Cache\Serializer\JsonSerializer;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Schema\Ddl\Column as DbColumn;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Event\EventInterface;
use Windwalker\ORM\Attributes\CurrentTime;
use Windwalker\ORM\Event\{AbstractSaveEvent,
    AfterDeleteEvent,
    AfterSaveEvent,
    AfterUpdateWhereEvent,
    BeforeDeleteEvent,
    BeforeSaveEvent,
    BeforeUpdateWhereEvent};
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\Reflection\ReflectAccessor;
use Windwalker\Utilities\TypeCast;

/**
 * EntityMapper is an entity & database mapping object.
 *
 * Similar to DataMapper pattern.
 */
class EntityMapper implements EventAwareInterface
{
    use EventAwareTrait;

    /**
     * @var ORM
     */
    protected ORM $orm;

    /**
     * @var EntityMetadata
     */
    protected EntityMetadata $metadata;

    /**
     * EntityManager constructor.
     *
     * @param  EntityMetadata  $metadata
     * @param  ORM             $orm
     */
    public function __construct(EntityMetadata $metadata, ORM $orm)
    {
        $this->orm      = $orm;
        $this->metadata = $metadata;

        $this->init();
    }

    protected function init(): void
    {
        //
    }

    /**
     * Create Query with select from.
     *
     * @param  mixed        $tables
     * @param  string|null  $alias
     *
     * @return  SelectorQuery
     */
    public function from(mixed $tables, ?string $alias = null): SelectorQuery
    {
        return $this->createSelectorQuery()->from($tables, $alias);
    }

    /**
     * Create Query with select.
     *
     * Use `select('...')` to select columns.
     * Use `select()` to create select query without any settings.
     *
     * @param  mixed  ...$columns
     *
     * @return  SelectorQuery
     */
    public function select(...$columns): SelectorQuery
    {
        return $this->createSelectorQuery()
            ->from($this->getMetadata()->getClassName())
            ->select(...$columns);
    }

    public function insert(bool $incrementField = false): SelectorQuery
    {
        return $this->createSelectorQuery()->insert($this->getMetadata()->getClassName(), $incrementField);
    }

    public function update(?string $alias = null): SelectorQuery
    {
        return $this->createSelectorQuery()->update($this->getMetadata()->getClassName() ,$alias);
    }

    public function delete(?string $alias = null): SelectorQuery
    {
        return $this->createSelectorQuery()->delete($this->getMetadata()->getClassName(), $alias);
    }

    /**
     * Create Selector query.
     *
     * @return  SelectorQuery
     */
    public function createSelectorQuery(): SelectorQuery
    {
        $selector = new SelectorQuery($this->getORM());

        $selector->getEventDispatcher()->addDealer($this->getEventDispatcher());

        return $selector;
    }

    /**
     * findOne
     *
     * @param  mixed  $conditions
     *
     * @return  object|null
     *
     * @throws \ReflectionException
     */
    public function findOne(mixed $conditions): ?object
    {
        $metadata = $this->getMetadata();

        return $this->from($metadata->getClassName())
            ->where($this->conditionsToWheres($conditions))
            ->get($metadata->getClassName());
    }

    public function findList(mixed $conditions = []): \Generator
    {
        $metadata = $this->getMetadata();

        return $this->from($metadata->getClassName())
            ->where($this->conditionsToWheres($conditions))
            ->getIterator($metadata->getClassName());
    }

    public function findResult(mixed $conditions): ?string
    {
        $metadata = $this->getMetadata();

        return $this->from($metadata->getClassName())
            ->where($this->conditionsToWheres($conditions))
            ->result();
    }

    public function createOne(array|object $source = []): object
    {
        $pk        = $this->getMainKey();
        $metadata  = $this->getMetadata();
        $aiColumn  = $this->getAutoIncrementColumn();
        $className = $metadata->getClassName();

        TypeAssert::assert(
            is_object($source) || is_array($source),
            '{caller} item must be array or object, {value} given',
            $source
        );

        $data = $this->extract($source);

        if ($aiColumn && isset($data[$aiColumn]) && !$data[$aiColumn]) {
            unset($data[$aiColumn]);
        }

        $type = AbstractSaveEvent::TYPE_CREATE;
        $event = $this->emitEvent(
            BeforeSaveEvent::class,
            compact('data', 'type', 'metadata', 'source')
        );

        $data = $this->castForSave($event->getData());

        $data = $this->getDb()->getWriter()->insertOne(
            $metadata->getTableName(),
            $data,
            $pk,
            [
                'incrementField' => $aiColumn && !empty($data[$aiColumn]),
            ]
        );

        if (is_array($source)) {
            $source = $this->getORM()->getAttributesResolver()->createObject($className);
        }

        $entity = $source;

        $event = $this->emitEvent(
            AfterSaveEvent::class,
            compact('data', 'type', 'metadata', 'entity', 'source')
        );

        $entity = $this->hydrate(
            $event->getData(),
            $event->getEntity()
        );

        $metadata->getRelationManager()->save($event->getData(), $entity);

        return $entity;
    }

    public function createMultiple(iterable $items): iterable
    {
        /** @var array|object $item */
        foreach ($items as $k => $item) {
            $items[$k] = $this->createOne($item);
        }

        return $items;
    }

    public function updateOne(
        array|object $source = [],
        array|string $condFields = null,
        bool $updateNulls = false
    ): ?StatementInterface {
        $metadata = $this->getMetadata();

        if (!$condFields) {
            $condFields = $this->getKeys();
        }

        if (!$condFields) {
            throw new \InvalidArgumentException(
                'Condition fields empty or Entity has no keys when updating data.'
            );
        }

        TypeAssert::assert(
            is_object($source) || is_array($source),
            '{caller} item must be array or object, {value} given',
            $source
        );

        $data = $this->extract($source);

        // Get old data
        $oldData = null;

        if ($this->getKeys() && !empty($data[$this->getMainKey()])) {
            $oldData = $this->getDb()->select('*')
                ->from($metadata->getTableName())
                ->where(Arr::only($data, $this->getKeys()))
                ->get()
                ?->dump();
        }

        $type = AbstractSaveEvent::TYPE_UPDATE;
        $event = $this->emitEvent(
            BeforeSaveEvent::class,
            compact('data', 'type', 'metadata', 'oldData', 'source')
        );

        $data = $this->castForSave($event->getData(), $updateNulls);

        $metadata = $event->getMetadata();

        $writeData = $data;

        $keyValues = Arr::only($writeData, (array) $condFields);
        if ($oldData !== null) {
            $writeData = array_diff_assoc($writeData, $oldData);
        }

        if ($writeData !== []) {
            $writeData = array_merge($keyValues, $writeData);

            $result = $this->getDb()->getWriter()->updateOne(
                $metadata->getTableName(),
                $writeData,
                $condFields,
                [
                    'updateNulls' => $updateNulls,
                ]
            );
        }

        $entity = $this->toEntity($source);

        $event = $this->emitEvent(
            AfterSaveEvent::class,
            compact('data', 'type', 'metadata', 'entity', 'oldData', 'source')
        );

        $metadata->getRelationManager()->save($event->getData(), $entity, $oldData);

        // Event

        return $result ?? null;
    }

    /**
     * updateMultiple
     *
     * @param  iterable           $items
     * @param  array|string|null  $condFields
     * @param  false              $updateNulls
     *
     * @return  StatementInterface[]
     *
     * @throws \JsonException
     */
    public function updateMultiple(iterable $items, array|string $condFields = null, bool $updateNulls = false): array
    {
        $results = [];

        foreach ($items as $k => $item) {
            $results[$k] = $this->updateOne($item, $condFields, $updateNulls);
        }

        // Event

        return $results;
    }

    /**
     * Using one data to update multiple rows, filter by where conditions.
     * Example:
     * `$mapper->updateWhere(new Data(array('published' => 0)), array('date' => '2014-03-02'))`
     * Means we make every records which date is 2014-03-02 unpublished.
     *
     * @param  mixed  $source      The data we want to update to every rows.
     * @param  mixed  $conditions  Where conditions, you can use array or Compare object.
     *
     * @return  bool
     * @throws \InvalidArgumentException
     */
    public function updateWhere(array|object $source, mixed $conditions = null): StatementInterface
    {
        $metadata = $this->getMetadata();

        $data = $this->extract($source);
        $fields = array_keys($data);
        $data = $this->castForSave($data);
        $data = Arr::only($data, $fields);

        // Event
        $event = $this->emitEvent(
            BeforeUpdateWhereEvent::class,
            compact('data', 'metadata', 'conditions', 'source')
        );

        $metadata = $event->getMetadata();

        $statement = $this->getDb()->getWriter()->updateWhere(
            $metadata->getTableName(),
            $data = $event->getData(),
            $conditions = $event->getConditions()
        );

        // Event
        $event = $this->emitEvent(
            AfterUpdateWhereEvent::class,
            compact('data', 'metadata', 'conditions', 'statement')
        );

        return $event->getStatement();
    }

    /**
     * updateWhere
     *
     * @param  array|object  $data
     * @param  mixed|null    $conditions
     * @param  bool          $updateNulls
     *
     * @return  StatementInterface[]
     */
    public function updateBatch(array|object $data, mixed $conditions = null, bool $updateNulls = false): array
    {
        $dataToSave = $this->extractForSave($data);

        $results = [];

        foreach ($this->findList($conditions) as $item) {
            $item = $this->hydrate($dataToSave, $item);

            $results[] = $this->updateOne($item, null, $updateNulls);
        }

        return $results;
    }

    public function saveMultiple(iterable $items, string|array $condFields = null, bool $updateNulls = false): iterable
    {
        // Event
        foreach ($items as $k => $item) {
            // Do save
            if ($this->isNew($item)) {
                $items[$k] = $this->createOne($item);
            } else {
                $this->updateOne($item, $condFields, $updateNulls);

                $items[$k] = $this->toEntity($item);
            }
        }

        // Event

        return $items;
    }

    public function isNew(array|object $item): bool
    {
        $aiColumnName = $this->getAutoIncrementColumn(true);

        if ($aiColumnName === null) {
            throw new \LogicException(
                sprintf(
                    '%s must has an auto-increment column in Entity to check isNew.',
                    $this->getMetadata()->getClassName()
                )
            );
        }

        $metadata = $this->getMetadata();

        if (
            $aiColumnName
            && is_object($item)
            && $metadata::isEntity($item)
            && $aiColumn = $metadata->getColumn($aiColumnName)
        ) {
            // If is Entity
            $aiPropName = $aiColumn->getName();
            $keyValue = ReflectAccessor::getValue($item, $aiPropName);
        } else {
            // Is array, object or Collection
            $keyValue = Arr::get($item, $aiColumnName);
        }

        return empty($keyValue);
    }

    public function saveOne(array|object $item, array|string $condFields = null, bool $updateNulls = false): object
    {
        return $this->saveMultiple([$item], $condFields, $updateNulls)[0];
    }

    public function findOneOrCreate(mixed $conditions, mixed $initData = null, bool $mergeConditions = true): object
    {
        $item = $this->findOne($conditions);

        if ($item) {
            return $item;
        }

        $item = [];

        if ($mergeConditions && is_array($conditions)) {
            foreach ($conditions as $k => $v) {
                if (!is_numeric($k)) {
                    $item[$k] = $v;
                }
            }
        }

        if (is_callable($initData)) {
            $result = $initData($item, $conditions);

            if ($result) {
                $item = $result;
            }
        } else {
            $initData = TypeCast::toArray($initData);

            foreach ($initData as $key => $value) {
                if ($value !== null) {
                    $item[$key] = $value;
                }
            }
        }

        return $this->createOne($item);
    }

    public function updateOneOrCreate(
        array|object $item,
        mixed $initData = null,
        ?array $condFields = null,
        bool $updateNulls = false
    ): object {
        $condFields = $condFields ?: $this->getKeys();

        $conditions = [];

        $data = $this->extract($item);

        foreach ($condFields as $field) {
            $conditions[$field] = $data[$field];
        }

        if ($found = $this->findOne($conditions)) {
            $this->updateOne($item, $condFields, $updateNulls);

            return $this->hydrate($item, $found);
        }

        if (is_callable($initData)) {
            $data = $initData($data, $conditions);
        } else {
            $initData = TypeCast::toArray($initData);

            foreach ($initData as $key => $value) {
                if ($value !== null) {
                    $data[$key] = $value;
                }
            }
        }

        return $this->createOne($data);
    }

    public function deleteWhere(mixed $conditions): array
    {
        // Event

        $metadata = $this->getMetadata();
        $writer   = $this->getDb()->getWriter();
        $entityObject = null;

        // Handle Entity
        if (is_object($conditions) && EntityMetadata::isEntity($conditions)) {
            $entityObject = $conditions;

            $conditions = Arr::only($this->extract($conditions), $this->getKeys());

            if (in_array(null, $conditions, true) || in_array('', $conditions, true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Unable to delete Entity: %s since the keys value contains NULL or empty string.',
                        $conditions::class
                    )
                );
            }
        }

        $keys = $this->getKeys();

        if (!$keys) {
            // If Entity has no keys, just use conditions to delete batch.
            $delItems = [$conditions];
        } elseif ($entityObject !== null) {
            $delItems = [$entityObject];
        } else {
            // If Entity has keys, use this keys to delete once per item.
            $delItems = $this->getORM()
                ->from($metadata->getClassName())
                ->where($this->conditionsToWheres($conditions))
                ->getIterator($metadata->getClassName());
        }

        $results = [];

        foreach ($delItems as $item) {
            if (!$keys) {
                $conditions = $this->conditionsToWheres($item);
                $data       = null;
                $entity     = null;
            } elseif ($entityObject !== null) {
                $entity = $entityObject;
                $data = $this->extract($entityObject);
                $conditions = Arr::only($data, $keys);
            } else {
                /** @var object $item */
                $entity = $item;
                $data = $this->extract($entity);
                $conditions = Arr::only($data, $keys);
            }

            // Event
            $event = $this->emitEvent(
                BeforeDeleteEvent::class,
                compact('data', 'conditions', 'metadata', 'entity')
            );

            $statement = $writer->delete($metadata->getTableName(), $conditions = $event->getConditions());

            // Event
            $event = $this->emitEvent(
                AfterDeleteEvent::class,
                compact('data', 'conditions', 'metadata', 'statement', 'entity')
            );

            $results[] = $event->getStatement();

            if ($event->getData() !== null) {
                $metadata->getRelationManager()->delete($event->getData(), $entity);
            }
        }

        // Event

        return $results;
    }

    public function flush(iterable $items, mixed $conditions = []): iterable
    {
        // Handling conditions
        $conditions = $this->conditionsToWheres($conditions);

        // Event

        $this->deleteWhere($conditions);

        $items = $this->createMultiple($items);

        // Event

        return $items;
    }

    public function sync(iterable $items, mixed $conditions = [], ?array $compareKeys = null): array
    {
        // Handling conditions
        $metadata   = $this->getMetadata();
        $conditions = $this->conditionsToWheres($conditions);

        $oldItems = $this->getORM()
            ->from($metadata->getClassName())
            ->where($conditions)
            ->all()
            ->dump(true);

        $compareKeys = $compareKeys ?? array_keys($conditions);

        // Event

        // Get diff
        $arrayItems = [];

        foreach ($items as $k => $item) {
            $arrayItems[$k] = $this->extract($item);
        }

        [$delItems,] = $this->getDeleteDiff($arrayItems, $oldItems, $compareKeys);
        [$createItems, $keepItems] = $this->getCreateDiff($arrayItems, $oldItems, $compareKeys);

        // Delete
        foreach ($delItems as $k => $delItem) {
            $this->deleteWhere(Arr::only($delItem, $compareKeys));

            $delItems[$k] = $this->toEntity($delItem);
        }

        // Create
        $createItems = $this->createMultiple($createItems);

        // Update
        foreach ($keepItems as $k => $keepItem) {
            $this->updateOne($keepItem);

            $keepItems[$k] = $this->toEntity($keepItem);
        }

        // Event

        return [$keepItems, $createItems, $delItems];
    }

    protected function getDeleteDiff(iterable $items, array $oldItems, array $compareKeys): array
    {
        $keep    = [];
        $deletes = [];

        foreach ($oldItems as $old) {
            $oldValues = Arr::only($old, $compareKeys);

            foreach ($items as $item) {
                // Check this old item has at-least 1 new item matched.
                if (Arr::arrayEquals($oldValues, Arr::only($item, $compareKeys))) {
                    $keep[] = $old;
                    continue 2;
                }
            }

            // If no matched, mark this old item to be delete.
            $deletes[] = $old;
        }

        return [$deletes, $keep];
    }

    protected function getCreateDiff(iterable $items, array $oldItems, array $compareKeys): array
    {
        $keep    = [];
        $creates = [];

        foreach ($items as $item) {
            $values = Arr::only($item, $compareKeys);

            foreach ($oldItems as $old) {
                // Check this new item has at-least 1 old item matched.
                if (Arr::arrayEquals(Arr::only($old, $compareKeys), $values)) {
                    $keep[] = $item;
                    continue 2;
                }
            }

            // If no matched, mark this new item to be create.
            $creates[] = $item;
        }

        return [$creates, $keep];
    }

    public function getKeys(): array
    {
        return $this->getMetadata()->getKeys();
    }

    public function getMainKey(): ?string
    {
        return $this->getMetadata()->getMainKey();
    }

    public function getTableName(): string
    {
        return $this->getMetadata()->getTableName();
    }

    public function createEntity(): object
    {
        $class = $this->getMetadata()->getClassName();

        return $this->getORM()->getAttributesResolver()->createObject($class);
    }

    public function toEntity(array|object $data): object
    {
        $class = $this->getMetadata()->getClassName();

        if ($data instanceof $class) {
            return $data;
        }

        if (is_object($data)) {
            $data = TypeCast::toArray($data);
        }

        return $this->getORM()->hydrateEntity(
            $data,
            $this->createEntity()
        );
    }

    public function hydrate(array $data, object $entity): object
    {
        return $this->getORM()->hydrateEntity($data, $entity);
    }

    public function extract(object|array $entity): array
    {
        if (is_array($entity)) {
            return $entity;
        }

        return $this->getORM()->extractEntity($entity);
    }

    public function extractField(object|array $entity, string $field): mixed
    {
        return $this->getORM()->extractField($entity, $field);
    }

    public function conditionsToWheres(mixed $conditions, ?string $alias = null): array
    {
        if (!is_array($conditions)) {
            $metadata = $this->getMetadata();

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

    protected function extractForSave(object|array $data, bool $updateNulls = true): array
    {
        $data = $this->extract($data);

        return $this->castForSave($data, $updateNulls);
    }

    protected function castForSave(array $data, bool $updateNulls = true): array
    {
        $metadata = $this->getMetadata();

        $item = [];

        $db       = $this->getDb();
        $dataType = $db->getPlatform()->getDataType();

        foreach ($this->getTableColumns() as $field => $column) {
            $value = $data[$field] ?? null;

            // Handler property attributes
            // TODO: Move to separate class or function
            if ($prop = $metadata->getColumn($field)?->getProperty()) {
                // Current Time
                if ($curr = AttributesAccessor::getFirstAttributeInstance($prop, CurrentTime::class)) {
                    $value = $curr->getCurrent();
                }
            }

            if (!$updateNulls && $value === null) {
                continue;
            }

            // Convert value type
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format($db->getDateFormat());
            }

            if ($value instanceof JsonSerializer) {
                $value = json_encode($value);
            }

            if (\is_object($value) && method_exists($value, '__toString')) {
                $value = (string) $value;
            }

            // Start prepare default value
            if (is_array($value) || is_object($value)) {
                $value = null;
            }

            if ($value === null) {
                // This field is null and the db column is not nullable, use db default value.
                if ($column->getIsNullable()) {
                    $item[$field] = null;
                } elseif ($column->getColumnDefault() !== null) {
                    $item[$field] = $column->getColumnDefault();
                } else {
                    $def          = $dataType::getDefaultValue($column->getDataType());
                    $item[$field] = $def !== false ? $def : '';
                }
            } elseif ($value === '') {
                // This field is null and the db column is not nullable, use db default value.
                if ($column->getIsNullable()) {
                    $item[$field] = null;
                } else {
                    $def          = $dataType::getDefaultValue($column->getDataType());
                    $item[$field] = $def !== false ? $def : '';
                }
            } else {
                $item[$field] = TypeCast::try(
                    $value,
                    $dataType::getPhpType($column->getDataType()),
                );
            }
        }

        return $item;
    }

    /**
     * getTableColumns
     *
     * @return  DbColumn[]
     */
    protected function getTableColumns(): array
    {
        return $this->getDb()
            ->getTable(
                $this->getMetadata()->getTableName()
            )
            ->getColumns();
    }

    protected function getAutoIncrementColumn(bool $checkDB = false): ?string
    {
        $ai = $this->getMetadata()->getAutoIncrementColumn()?->getName();

        if ($ai) {
            return $ai;
        }

        if ($checkDB) {
            foreach ($this->getTableColumns() as $column) {
                if ($column->isAutoIncrement()) {
                    return $column->getColumnName();
                }
            }
        }

        return null;
    }

    /**
     * @return ORM
     */
    public function getORM(): ORM
    {
        return $this->orm;
    }

    public function getDb(): DatabaseAdapter
    {
        return $this->getORM()->getDb();
    }

    /**
     * @return EntityMetadata
     */
    public function getMetadata(): EntityMetadata
    {
        return $this->metadata;
    }

    public function emitEvent(EventInterface|string $event, array $args = []): EventInterface
    {
        $event = $this->emit($event, $args);

        $methods = $this->getMetadata()->getMethodsOfAttribute($event::class);

        foreach ($methods as $method) {
            $result = $this->getORM()->getAttributesResolver()->call(
                $method->getClosure(),
                [
                    $event::class => $event,
                    'event' => $event
                ]
            );

            if ($result instanceof EventInterface) {
                $event = $result;
            }
        }

        return $event;
    }

    protected function hasEvents(...$events): bool
    {
        foreach ($events as $event) {
            foreach ($this->getEventDispatcher()->getListeners($event) as $listener) {
                return true;
            }

            $methods = $this->getMetadata()->getMethodsOfAttribute($event);

            if ($methods !== []) {
                return true;
            }
        }

        return false;
    }
}
