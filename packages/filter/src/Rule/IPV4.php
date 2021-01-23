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
class IPV4 extends AbstractRegexFilter
{
    /**
     * @inheritDoc
     */
    public function getRegex(): string
    {
        return '/[^0-9.]/i';
    }

    /**
     * @inheritDoc
     *
     * @see https://stackoverflow.com/a/36760050
     */
    public function test(mixed $value, bool $strict = false): bool
    {
        return (bool) preg_match('/^((25[0-5]|(2[0-4]|1[0-9]|[1-9]|)[0-9])(\.(?!$)|$)){4}$/', (string) $value);
    }
}
