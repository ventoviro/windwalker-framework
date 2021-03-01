<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Clause\ValueClause;

if (!function_exists('clause')) {
    /**
     * clause
     *
     * @param  string        $name
     * @param  array|string  $elements
     * @param  string        $glue
     *
     * @return  Clause
     */
    function clause(string $name = '', $elements = [], string $glue = ' '): Clause
    {
        return new Clause($name, $elements, $glue);
    }
}

if (!function_exists('val')) {
    /**
     * val
     *
     * @param  mixed  $value
     *
     * @return  ValueClause|array
     */
    function val(mixed $value): ValueClause|array
    {
        if ($value instanceof ValueClause) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(fn ($v) => new ValueClause($v), $value);
        }

        return new ValueClause($value);
    }
}
