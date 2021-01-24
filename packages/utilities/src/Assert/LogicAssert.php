<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Assert;

use LogicException;

/**
 * The LogicAssert class.
 */
class LogicAssert extends TypeAssert
{
    protected static function exception(): callable
    {
        return fn(string $message) => new LogicException($message);
    }
}
