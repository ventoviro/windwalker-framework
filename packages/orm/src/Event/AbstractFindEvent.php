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

/**
 * The AbstractFindEvent class.
 */
class AbstractFindEvent extends AbstractEvent
{
    protected mixed $conditions;


}
