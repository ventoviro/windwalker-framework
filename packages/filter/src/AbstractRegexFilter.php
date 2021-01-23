<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter;

use function RingCentral\Psr7\str;

/**
 * The AbstractRegex class.
 */
abstract class AbstractRegexFilter extends AbstractFilter
{
    public const TYPE_MATCH = 'match';
    public const TYPE_REPLACE = 'replace';

    protected string $type = self::TYPE_REPLACE;

    /**
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        $type = $this->type;

        return $this->$type($value);
    }

    /**
     * Method to get property Regex
     *
     * @return  string
     */
    abstract public function getRegex(): string;

    public function match(string $value): string
    {
        return preg_match($this->getRegex(), $value)[0] ?? '';
    }

    /**
     * replace
     *
     * @param  string  $value
     *
     * @return string|null
     */
    public function replace(string $value): string
    {
        return preg_replace($this->getRegex(), '', $value) ?? '';
    }
}
