<?php

/**
 * Part of Windwalker Packages project.
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
use Windwalker\Cache\Exception\LogicException;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The AutoIncrement class.
 */
#[Attribute]
class AutoIncrement implements AttributeInterface
{
    use ORMAttributeTrait;

    /**
     * @inheritDoc
     */
    public function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        /** @var ReflectionProperty $prop */
        $prop = $handler->getReflector();

        $metadata->addAttributeMap(static::class, $prop);

        $column = $metadata->getColumnByPropertyName($prop->getName());

        if ($column === null) {
            throw new LogicException(Column::class . ' attribute must before ' . static::class);
        }

        $setter = function () use ($column) {
            $this->aiColumn = $column;
        };

        $setter->call($metadata);

        return $handler->get();
    }
}
