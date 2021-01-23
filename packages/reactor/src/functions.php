<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker;

/**
 * go
 *
 * @param  callable    $handler
 * @param  array|null  $params
 *
 * @return  mixed
 */
function go(callable $handler, $params = null): mixed
{
    if (function_exists('\go')) {
        return \go($handler, $params);
    }

    return $handler();
}
