<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractCallbackFilter;

/**
 * The Nagtive class.
 */
class Negative extends AbstractCallbackFilter
{
    /**
     * @inheritDoc
     */
    public function getHandler(): callable
    {
        return fn ($value) => -$value;
    }

    /**
     * @inheritDoc
     */
    public function test(mixed $value, bool $strict = false): bool
    {
        return $value < 0;
    }
}
