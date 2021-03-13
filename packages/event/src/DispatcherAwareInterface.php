<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Event;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Interface DispatcherAwareInterface
 */
interface DispatcherAwareInterface
{
    /**
     * getDispatcher
     *
     * @return  EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface;
}
