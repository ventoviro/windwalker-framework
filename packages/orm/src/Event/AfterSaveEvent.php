<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Windwalker\Database\Driver\StatementInterface;

/**
 * The BeforeSaveEvent class.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class AfterSaveEvent extends AbstractSaveEvent
{
    protected object $entity;

    /**
     * @return object
     */
    public function getEntity(): object
    {
        return $this->entity;
    }

    /**
     * @param  object  $entity
     *
     * @return  static  Return self to support chaining.
     */
    public function setEntity(object $entity): static
    {
        $this->entity = $entity;

        return $this;
    }
}
