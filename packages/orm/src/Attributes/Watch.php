<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use ReflectionProperty;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\ORM\Event\AbstractSaveEvent;
use Windwalker\ORM\Event\AbstractUpdateWhereEvent;
use Windwalker\ORM\Event\WatchEvent;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The Watch class.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class Watch implements AttributeInterface
{
    use ORMAttributeTrait;

    public const BEFORE_SAVE = 1 << 0;

    public const ON_CREATE = 1 << 1;

    public const INCLUDE_UPDATE_WHERE = 1 << 2;

    /**
     * @var callable|string
     */
    protected $columnOrHandler;

    /**
     * Watch constructor.
     */
    public function __construct(
        string|callable $columnOrHandler,
        public int $options = 0,
    ) {
        $this->columnOrHandler = $columnOrHandler;
    }

    protected function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        $metadata->addAttributeMap(static::class, $handler->getReflector());

        return function () use ($handler, $metadata) {
            $ref = $handler->getReflector();
            $target = $handler();

            if ($ref instanceof ReflectionProperty) {
                $method = $this->columnOrHandler;
                $column = $metadata->getColumnByPropertyName($ref->getName())->getName();
            } else {
                $method = $target;
                $column = $this->columnOrHandler;
            }

            $metadata->watch($column, $method, $this->options);

            return $method;
        };
    }

    public static function createWatchEvent(
        AbstractSaveEvent|AbstractUpdateWhereEvent $event,
        mixed $value,
        mixed $oldValue = null,
    ): WatchEvent {
        $watchEvent = (new WatchEvent())
            ->setOriginEvent($event)
            ->setValue($value)
            ->setData($event->getData())
            ->setSource($event->getData());

        if ($event instanceof AbstractSaveEvent) {
            $watchEvent->setOldData([]);
            $watchEvent->setOldValue($oldValue);
            $watchEvent->setIsUpdateWhere(false);
        } else {
            $watchEvent->setOldData($event->getData());
            $watchEvent->setOldValue(null);
            $watchEvent->setIsUpdateWhere(true);
        }

        return $watchEvent;
    }
}
