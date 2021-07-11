<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Exception;

use BadMethodCallException;
use Windwalker\Utilities\Assert\Assert;

/**
 * The ExceptionFactory class.
 */
class ExceptionFactory
{
    /**
     * badMethodCall
     *
     * @param  string       $name
     * @param  string|null  $caller
     *
     * @return BadMethodCallException
     */
    public static function badMethodCall(string $name, ?string $caller = null): BadMethodCallException
    {
        return new BadMethodCallException(
            sprintf(
                'Call to undefined method: %s::%s()',
                $caller ?? Assert::getCaller(2),
                $name
            )
        );
    }
}
