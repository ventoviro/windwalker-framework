<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * The DependencyResolutionException class.
 */
class DependencyResolutionException extends Exception implements ContainerExceptionInterface
{
}
