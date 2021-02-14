<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM;

use Windwalker\Attributes\AttributesResolver;
use Windwalker\Cache\Serializer\JsonSerializer;
use Windwalker\Data\Collection;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Schema\Ddl\Column as DbColumn;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Event\EventInterface;
use Windwalker\ORM\Attributes\CurrentTime;
use Windwalker\ORM\Event\{
    AbstractSaveEvent,
    AfterDeleteEvent,
    AfterSaveEvent,
    AfterUpdateBatchEvent,
    BeforeDeleteEvent,
    BeforeSaveEvent,
    BeforeUpdateBatchEvent
};
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Strategy\Selector;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Assert\TypeAssert;
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
    }

    /**
     * Create Query with select from.
     *
     * @param  mixed        $tables
     * @param  string|null  $alias
     *
     * @return  Selector
     */
    public function from(mixed $tables, ?string $alias = null): Selector
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
     * @return  Selector
     */
    public function select(...$columns): Selector
    {
        return $this->createSelectorQuery()
            ->from($this->getMetadata()->getClassName())
            ->select(...$columns);
    }

    /**
     * Create Selector query.
     *
     * @return  Selector
     */
    public function createSelectorQuery(): Selector
    {
        return (new Selector($this->getORM()));
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

    public function findResult(mixed $conditions): ?string
    {
        $metadata = $this->getMetadata();

        return $this->from($metadata->getClassName())
            ->where($this->conditionsToWheres($conditions))
            ->result();
    }

    public function createOne(array|object $item = []): array|object
    {
        $items = $this->createMultiple(
            [$item]
        );

        return $items[0];
    }

    public function createMultiple(iterable $items): iterable
    {
        $pk        = $this->getMainKey();
        $metadata  = $this->getMetadata();
        $aiColumn  = $this->getAutoIncrementColumn();
        $className = $metadata->getClassName();

        /** @var array|object $item */
        foreach ($items as $k => $item) {
            TypeAssert::assert(
                is_object($item) || is_array($item),
                '{caller} item must be array or object, {value} given',
                $item
            );

            $data = $this->extractForSave($item);

            if ($aiColumn && isset($data[$aiColumn]) && !$data[$aiColumn]) {
                unset($data[$aiColumn]);
            }

            $type = AbstractSaveEvent::TYPE_CREATE;
            $event = $this->emitEvent(
                BeforeSaveEvent::class,
                compact('data', 'type', 'metadata')
            );

            $this->getDb()->getWriter()->insertOne(
                $metadata->getTableName(),
                $data = $event->getData(),
                $pk,
                [
                    'incrementField' => $aiColumn && isset($data[$aiColumn]),
                ]
            );

            if (is_array($item)) {
                $item = $this->getORM()->getAttributesResolver()->createObject($className);
            }

            $entity = $item;

            $event = $this->emitEvent(
                AfterSaveEvent::class,
                compact('data', 'type', 'metadata', 'entity')
            );

            $items[$k] = $entity = $this->hydrate(
                $event->getData(),
                $event->getEntity()
            );

            $metadata->getRelationManager()->save($event->getData(), $entity);
        }

        // Event

        return $items;
    }

    public function updateOne(
        array|object $item = [],
        array|string $condFields = null,
        bool $updateNulls = false
    ): StatementInterface {
        return $this->updateMultiple([$item], $condFields, $updateNulls)[0];
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
    public function updateMultiple(iterable $items, array|string $condFields = null, $updateNulls = false): array
    {
        $metadata = $this->getMetadata();

        if (!$condFields) {
            $condFields = $this->getKeys();
        }

        $results = [];

        foreach ($items as $k => $item) {
            TypeAssert::assert(
                is_object($item) || is_array($item),
                '{caller} item must be array or object, {value} given',
                $item
            );

            $data = $this->extractForSave($item, $updateNulls);

            $type = AbstractSaveEvent::TYPE_UPDATE;
            $event = $this->emitEvent(
                BeforeSaveEvent::class,
                compact('data', 'type', 'metadata')
            );

            $metadata = $event->getMetadata();

            $results[] = $this->getDb()->getWriter()->updateOne(
                $metadata->getTableName(),
                $data = $event->getData(),
                $condFields,
                [
                    'updateNulls' => $updateNulls,
                ]
            );

            $entity = $this->toEntity($data);

            $this->emitEvent(
                AfterSaveEvent::class,
                compact('data', 'type', 'metadata', 'entity')
            );
        }

        // Event

        return $results;
    }

    /**
     * Using one data to update multiple rows, filter by where conditions.
     * Example:
     * `$mapper->updateAll(new Data(array('published' => 0)), array('date' => '2014-03-02'))`
     * Means we make every records which date is 2014-03-02 unpublished.
     *
     * @param  mixed  $data        The data we want to update to every rows.
     * @param  mixed  $conditions  Where conditions, you can use array or Compare object.
     *                             Example:
     *                             - `array('id' => 5)` => id = 5
     *                             - `new GteCompare('id', 20)` => 'id >= 20'
     *                             - `new Compare('id', '%Flower%', 'LIKE')` => 'id LIKE "%Flower%"'
     *
     * @return  boolean
     * @throws \InvalidArgumentException
     */
    public function updateBatch(array|object $data, mixed $conditions = null): StatementInterface
    {
        $metadata = $this->getMetadata();
        $data = $this->extract($data);

        // Event
        $event = $this->emitEvent(
            BeforeUpdateBatchEvent::class,
            compact('data', 'metadata', 'conditions')
        );

        $metadata = $event->getMetadata();

        $statement = $this->getDb()->getWriter()->updateBatch(
            $metadata->getTableName(),
            $data = $event->getData(),
            $conditions = $event->getConditions()
        );

        // Event
        $event = $this->emitEvent(
            AfterUpdateBatchEvent::class,
            compact('data', 'metadata', 'conditions', 'statement')
        );

        return $event->getStatement();
    }

    public function saveMultiple(iterable $items, string|array $condFields = null, bool $updateNulls = false): iterable
    {
        // Event

        $aiColumn = $this->getAutoIncrementColumn(true);

        TypeAssert::assert(
            $aiColumn !== null,
            '{caller} must has an auto-increment column in Entity to separate update and create.'
        );

        foreach ($items as $k => $item) {
            $update = !empty($item[$aiColumn]);

            // Do save
            if ($update) {
                $this->updateOne($item, $condFields, $updateNulls);

                $items[$k] = $this->toEntity($item);
            } else {
                $items[$k] = $this->createOne($item);
            }
        }

        // Event

        return $items;
    }

    public function saveOne(array|object $item, array|string $condFields = null, bool $updateNulls = false)
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

    public function delete(mixed $conditions): array
    {
        // Event

        $metadata = $this->getMetadata();
        $writer   = $this->getDb()->getWriter();

        // Handle Entity
        if (is_object($conditions) && EntityMetadata::isEntity($conditions)) {
            $data = $this->extract($conditions);

            $conditions = Arr::only($data, $this->getKeys());

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
        } else {
            // If Entity has keys, use this keys to delete once per item.
            $delItems = $this->getORM()
                ->from($metadata->getClassName())
                ->where($this->conditionsToWheres($conditions));
        }

        $results = [];

        foreach ($delItems as $item) {
            if (!$keys) {
                $conditions = $this->conditionsToWheres($item);
                $data       = null;
            } else {
                /** @var Collection $item */
                $conditions = $item->only($keys)->dump();
                $data = $item->dump(true);
            }

            // Event
            $event = $this->emitEvent(
                BeforeDeleteEvent::class,
                compact('data', 'conditions', 'metadata')
            );

            $statement = $writer->delete($metadata->getTableName(), $conditions = $event->getConditions());

            // Event
            $event = $this->emitEvent(
                AfterDeleteEvent::class,
                compact('data', 'conditions', 'metadata', 'statement')
            );

            $results[] = $event->getStatement();
        }

        // Event

        return $results;
    }

    public function flush(iterable $items, mixed $conditions = []): iterable
    {
        // Handling conditions
        $conditions = $this->conditionsToWheres($conditions);

        // Event

        $this->delete($conditions);

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
            $this->delete(Arr::only($delItem, $compareKeys));

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
            ksort($oldValues);

            $matched = false;

            foreach ($items as $item) {
                $values = Arr::only($item, $compareKeys);
                ksort($values);

                // Check this old item has at-least 1 new item matched.
                $matched = $matched || $oldValues === $values;
            }

            // If no matched, mark this old item to be delete.
            if ($matched) {
                $keep[] = $old;
            } else {
                $deletes[] = $old;
            }
        }

        return [$deletes, $keep];
    }

    protected function getCreateDiff(iterable $items, array $oldItems, array $compareKeys): array
    {
        $keep    = [];
        $creates = [];

        foreach ($items as $item) {
            $values = Arr::only($item, $compareKeys);
            ksort($values);

            $matched = false;

            foreach ($oldItems as $old) {
                $oldValues = Arr::only($old, $compareKeys);
                ksort($oldValues);

                // Check this new item has at-least 1 old item matched.
                $matched = $matched || $oldValues === $values;
            }

            // If no matched, mark this new item to be create.
            if ($matched) {
                $keep[] = $item;
            } else {
                $creates[] = $item;
            }
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

    public function toEntity(array|object $data): object
    {
        $class = $this->getMetadata()->getClassName();

        if ($data instanceof $class) {
            return $data;
        }

        return $this->getORM()->hydrateEntity(
            $data,
            $this->getORM()->getAttributesResolver()->createObject($class)
        );
    }

    public function hydrate(array $data, object $entity): object
    {
        return $this->getORM()->hydrateEntity($data, $entity);
    }

    public function extract(object|array $entity): array
    {
        return $this->getORM()->extractEntity($entity);
    }

    public function conditionsToWheres(mixed $conditions): array
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
                if ($curr = AttributesResolver::getFirstAttributeInstance($prop, CurrentTime::class)) {
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
}
