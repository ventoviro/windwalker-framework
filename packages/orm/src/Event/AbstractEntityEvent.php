<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Event\AbstractEvent;
use Windwalker\ORM\Attributes\ORMAttributeTrait;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;

/**
 * The AbstractEntityEvent class.
 */
class AbstractEntityEvent extends AbstractEvent implements AttributeInterface
{
    use ORMAttributeTrait;

    protected EntityMetadata $metadata;

    /**
     * @return EntityMetadata
     */
    public function getMetadata(): EntityMetadata
    {
        return $this->metadata;
    }

    /**
     * @param  EntityMetadata  $metadata
     *
     * @return  static  Return self to support chaining.
     */
    public function setMetadata(EntityMetadata $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getORM(): ORM
    {
        return $this->getMetadata()->getORM();
    }

    public function getDb(): DatabaseAdapter
    {
        return $this->getORM()->getDb();
    }

    /**
     * @inheritDoc
     */
    protected function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        $metadata->addAttributeMap(static::class, $handler->getReflector());

        return $handler->get();
    }
}
