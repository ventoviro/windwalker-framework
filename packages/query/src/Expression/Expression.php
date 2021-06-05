<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Expression;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;
use Windwalker\Utilities\StrNormalize;

/**
 * Class QueryExpression
 *
 * @method Clause year(string $field)
 * @method Clause month(string $field)
 * @method Clause day(string $field)
 * @method Clause hour(string $field)
 * @method Clause minute(string $field)
 * @method Clause second(string $field)
 * @method Clause length(string $field)
 * @method Clause currentTimestamp()
 * @method Clause charLength(string $field)
 *
 * @since 2.0
 */
class Expression
{
    /**
     * Property query.
     *
     * @var  Query
     */
    protected $query = null;

    /**
     * @param $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * isExpression
     *
     * @param   string $value
     *
     * @return  boolean
     */
    public static function isExpression(string $value): bool
    {
        return substr($value, -1) === ')';
    }

    /**
     * buildExpression
     *
     * @param  string  $name
     * @param  array   $args
     *
     * @return  mixed|Clause
     */
    public function build(string $name, ...$args): mixed
    {
        $method = StrNormalize::toCamelCase($name);

        if (method_exists($this, $method)) {
            return $this->$method(...$args);
        }

        return $this->query->clause(strtolower($name) . '()', $args, ', ');
    }

    /**
     * getQuery
     *
     * @return  Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * setQuery
     *
     * @param  Query  $query
     *
     * @return  static  Return self to support chaining.
     */
    public function setQuery(Query $query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Concatenates an array of column names or values.
     *
     * Usage:
     * $query->select($query->concat(['a', 'b']));
     *
     * @param   array  $values    An array of values to concatenate.
     * @param   string $separator As separator to place between each value.
     *
     * @return  Clause  The concatenated values.
     *
     * @since   2.0
     */
    public function concat(array $values, ?string $separator = null): Clause
    {
        return $this->query->clause(
            'CONCATENATE()',
            $values,
            $separator !== null
                ? ' || ' . $this->query->quote($separator) . ' || '
                : ' || '
        );
    }

    /**
     * caseCondition
     *
     * @param   array  $cases
     * @param   string $else
     *
     * @return  string
     *
     * @since   2.1
     */
    public function caseSwitch(array $cases, $else = null): string
    {
        $expression = 'CASE';

        foreach ($cases as $condition => $case) {
            $expression .= ' WHEN ' . $condition . ' THEN ' . $case . "\n";
        }

        if ($else) {
            $expression .= ' ELSE ' . $else . "\n";
        }

        $expression .= ' END';

        return $expression;
    }

    public function __call($name, $args)
    {
        return $this->build(StrNormalize::toSnakeCase($name), ...$args);
    }
}
