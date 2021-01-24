<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Exception;

use Psr\SimpleCache\CacheException;

/**
 * The CacheExceptionInterface class.
 */
interface CacheExceptionInterface extends
    \Psr\Cache\CacheException,
    CacheException
{
}
