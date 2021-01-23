<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractRegexFilter;

/**
 * The IPAddress class.
 */
class IPAddress extends AbstractRegexFilter
{
    /**
     * @inheritDoc
     */
    public function getRegex(): string
    {
        return '/[^A-F0-9\.:\[\]]/i';
    }

    /**
     * @inheritDoc
     */
    public function test(mixed $value, bool $strict = false): bool
    {
        return (bool) filter_var(
            $value,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV6 | FILTER_FLAG_IPV4
        );
    }
}
