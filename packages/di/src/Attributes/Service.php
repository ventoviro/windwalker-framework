<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use Attribute;
use Windwalker\DI\Container;

/**
 * The Service class.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Service extends Inject
{
    protected function createObject(Container $container, string $id): object
    {
        return $container->createSharedObject($id);
    }
}
