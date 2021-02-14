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
 * The AfterUpdateBatchEvent class.
 */
#[\Attribute]
class AfterUpdateBatchEvent extends AbstractUpdateBatchEvent
{
    protected StatementInterface $statement;

    /**
     * @return StatementInterface
     */
    public function getStatement(): StatementInterface
    {
        return $this->statement;
    }

    /**
     * @param  StatementInterface  $statement
     *
     * @return  static  Return self to support chaining.
     */
    public function setStatement(StatementInterface $statement): static
    {
        $this->statement = $statement;

        return $this;
    }
}
