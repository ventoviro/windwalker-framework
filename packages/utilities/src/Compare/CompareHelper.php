<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Compare;

use InvalidArgumentException;
use Windwalker\Utilities\TypeCast;

/**
 * The CompareHelper class.
 *
 * @since  2.0
 */
class CompareHelper
{
    /**
     * Compare two values.
     *
     * @param  mixed        $a         The compare1 value.
     * @param  mixed        $b         The compare2 calue.
     * @param  string|null  $operator  The compare operator.
     * @param  bool         $strict    Use strict compare.
     *
     * @return  bool|int
     *
     * @throws InvalidArgumentException
     */
    public static function compare(mixed $a, mixed $b, ?string $operator = null, bool $strict = false): int|bool
    {
        if ($operator === null) {
            return $a <=> $b;
        }

        $operator = strtolower(trim($operator));

        switch ($operator) {
            case '=':
            case '==':
            case 'eq':
                return $strict ? $a === $b : $a == $b;

            case '===':
                return $a === $b;

            case '!=':
            case 'neq':
                return $strict ? $a !== $b : $a != $b;

            case '!==':
                return $a !== $b;

            case '>':
            case 'gt':
                return $a > $b;

            case '>=':
            case 'gte':
                return $a >= $b;

            case '<':
            case 'lt':
                return $a < $b;

            case '<=':
            case 'lte':
                return $a <= $b;

            case 'in':
                return in_array($a, TypeCast::toArray($b), $strict);

            case 'not in':
            case 'not-in':
            case 'notin':
            case 'nin':
                return !in_array($a, TypeCast::toArray($b), $strict);

            default:
                throw new InvalidArgumentException('Invalid compare operator: ' . $operator);
        }
    }
}
