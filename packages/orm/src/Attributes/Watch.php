<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\ORM\Event\AbstractSaveEvent;
use Windwalker\ORM\Event\AbstractUpdateWhereEvent;
use Windwalker\ORM\Event\AfterSaveEvent;
use Windwalker\ORM\Event\AfterUpdateWhereEvent;
use Windwalker\ORM\Event\BeforeSaveEvent;
use Windwalker\ORM\Event\BeforeUpdateWhereEvent;
use Windwalker\ORM\Event\WatchEvent;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The Watch class.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Watch implements AttributeInterface
{
    use ORMAttributeTrait;

    public const BEFORE_SAVE = 1 << 0;

    public const ON_CREATE = 1 << 1;

    public const INCLUDE_UPDATE_WHERE = 1 << 2;

    /**
     * Watch constructor.
     */
    public function __construct(
        public string $field,
        public int $options = 0,
    ) {
    }

    protected function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        $metadata->addAttributeMap(static::class, $handler->getReflector());

        return function () use ($handler, $metadata) {
            $method = $handler();

            if ($this->options & static::BEFORE_SAVE) {
                $metadata->on(
                    BeforeSaveEvent::class,
                    function (BeforeSaveEvent $event) use ($handler, $method) {
                        if (!($this->options & static::ON_CREATE) && $event->getType() === BeforeSaveEvent::TYPE_CREATE) {
                            return;
                        }

                        $val    = $event->getData()[$this->field] ?? null;
                        $oldVal = $event->getOldData()[$this->field] ?? null;

                        if ($val !== $oldVal) {
                            $watchEvent = self::createWatchEvent($event, $val, $oldVal);

                            $handler->getResolver()
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
                $metadata->on(
                    BeforeUpdateWhereEvent::class,
                    function (BeforeUpdateWhereEvent $event) use ($method, $handler) {
                        $val = $event->getData()[$this->field] ?? null;

                        $watchEvent = self::createWatchEvent($event, $val);

                        $handler->getResolver()
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
                $metadata->on(
                    AfterSaveEvent::class,
                    function (AfterSaveEvent $event) use ($handler, $method) {
                        if (!($this->options & static::ON_CREATE) && $event->getType() === AfterSaveEvent::TYPE_CREATE) {
                            return;
                        }

                        $val    = $event->getData()[$this->field] ?? null;
                        $oldVal = $event->getOldData()[$this->field] ?? null;

                        if ($val !== $oldVal) {
                            $watchEvent = self::createWatchEvent($event, $val, $oldVal);

                            $handler->getResolver()
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
                $metadata->on(
                    AfterUpdateWhereEvent::class,
                    function (AfterUpdateWhereEvent $event) use ($method, $handler) {
                        $val = $event->getData()[$this->field] ?? null;

                        $watchEvent = self::createWatchEvent($event, $val);

                        $handler->getResolver()
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

            return $method;
        };
    }

    protected static function createWatchEvent(
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
