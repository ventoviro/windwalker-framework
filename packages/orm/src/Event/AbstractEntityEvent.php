<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Windwalker\Event\AbstractEvent;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The AbstractEntityEvent class.
 */
class AbstractEntityEvent extends AbstractEvent
{
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
}
