<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Event\Attributes;

use Attribute;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

/**
 * The EventSubscriber class.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class EventSubscriber implements AttributeInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(AttributeHandler $handler): callable
    {
        //
    }
}
