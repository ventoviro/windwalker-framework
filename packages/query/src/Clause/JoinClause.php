<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Clause;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use Windwalker\Query\Query;
use Windwalker\Utilities\Assert\ArgumentsAssert;
use Windwalker\Utilities\Wrapper\RawWrapper;

use function Windwalker\Query\val;

/**
 * The JoinClause class.
 *
 * @method string raw($value, ...$args)
 * @method string format(string $text, ...$args)
 * @method Clause clause(string $name, $elements = [], string $glue = ' ')
 * @method string dateFormat()
 * @method string nullDate()
 * @method mixed  escape($values)
 * @method mixed  quote($values)
 * @method mixed  quoteName($values)
 */
class JoinClause implements ClauseInterface
{
    /**
     * @var string|AsClause
     */
    protected $table;

    /**
     * @var Query
     */
    protected $query;

    /**
     * @var Clause
     */
    protected $on;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * JoinClause constructor.
     *
     * @param  Query            $query
     * @param  string           $prefix
     * @param  string|AsClause  $table
     */
    public function __construct(Query $query, string $prefix, AsClause|string $table)
    {
        $this->table = $table;
        $this->query = $query;
        $this->prefix = $prefix;
    }

    /**
     * on
     *
     * @param  string|array|Closure|ClauseInterface  $column   Column name, array where list or callback
     *                                                         function as sub query.
     * @param  mixed                                 ...$args
     *
     * @return  static
     */
    public function on(mixed $column, ...$args): static
    {
        if ($column instanceof Closure) {
            $this->handleNestedOn($column, (string) ($args[0] ?? 'AND'));

            return $this;
        }

        if (is_array($column)) {
            foreach ($column as $where) {
                $this->on(...$where);
            }

            return $this;
        }

        $column = $this->query->as($column, false);

        [$operator, $value] = $this->handleOperatorAndValue(
            $args[0] ?? null,
            $args[1] ?? null,
            count($args) === 1
        );

        $this->onRaw(
            $this->query->clause(
                '',
                [$column, $operator, $value]
            )
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param  string  $prefix
     *
     * @return  static  Return self to support chaining.
     */
    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    private function val($value): ValueClause
    {
        return new ValueClause($value);
    }

    /**
     * Handle value and operator.
     *
     * This method will wrap value as a ValueObject and inject into bounded params.
     * By default, where clause uses `?` as placeholder and bind variables to prepared statement.
     * But it is hard to handle sub queries' placeholder ordering.
     *
     * ValueObject will be injected to bounded temporaries so that we can change `?` to
     * a named param like: `:wqp__{ordering}` and re-calc the order when every time rendering Query object,
     * so we can make sure the variables won't be conflict.
     *
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  bool   $shortcut
     *
     * @return  array
     */
    private function handleOperatorAndValue(mixed $operator, mixed $value, bool $shortcut = false): array
    {
        if ($shortcut) {
            [$operator, $value] = ['=', $operator];
        }

        if ($operator === null) {
            throw new InvalidArgumentException('Where operator should not be NULL');
        }

        // Closure means to create a sub query as value.
        if ($value instanceof Closure) {
            $value($value = $this->query->createSubQuery());
        }

        // Keep origin value a duplicate that we will need it later.
        // The $value will make it s a ValueClause object and inject to bounded params,
        // so that we can use it to generate prepared param placeholders.
        // The $origin variable is to store origin value at Query object if needed.
        $origin = $value;

        if ($value === null) {
            if ($operator === '=') {
                $operator = 'IS';
            } elseif ($operator === '!=') {
                $operator = 'IS NOT';
            }

            $value = 'NULL';
        } elseif (is_array($value)) {
            // Auto convert array value as IN() clause.
            if ($operator === '=') {
                $operator = 'IN';
            } elseif ($operator === '!=') {
                $operator = 'NOT IN';
            }

            $value = $this->query->clause('()', [], ', ');

            foreach ($origin as $col) {
                // Append every value as ValueObject so that we can make placeholders as `IN(?, ?, ?...)`
                $value->append($vc = val($col));

                $this->query->bind(null, $vc);
            }
        } elseif ($value instanceof Query) {
            $value = $this->query->as($origin, false);
        } elseif ($value instanceof RawWrapper) {
            $value = $value();
        } elseif ($value instanceof ValueClause) {
            $value = clone $value;
            $this->query->bind(null, $value);
        } else {
            $value = $this->query->quoteName($value);
        }

        return [strtoupper($operator), $value, $origin];
    }

    private function handleNestedOn(Closure $callback, string $glue): void
    {
        if (!in_array(strtolower(trim($glue)), ['and', 'or'], true)) {
            throw new InvalidArgumentException('WHERE glue should only be `OR`, `AND`.');
        }

        $callback($clause = new static($this->query, $this->prefix, $this->table));

        /** @var Clause $clause */
        $clause = $clause->on;

        // If where clause not exists, means this callback has no where call, just return.
        if (!$clause) {
            return;
        }

        $this->onRaw(
            $clause->setName('()')
                ->setGlue(' ' . strtoupper($glue) . ' ')
        );
    }

    /**
     * orWhere
     *
     * @param  array|Closure  $wheres
     *
     * @return  static
     */
    public function orOn(array|Closure $wheres): static
    {
        if (is_array($wheres)) {
            return $this->orOn(
                static function (JoinClause $join) use ($wheres) {
                    foreach ($wheres as $where) {
                        $join->on(...$where);
                    }
                }
            );
        }

        ArgumentsAssert::assert(
            $wheres instanceof Closure,
            '{caller} argument should be array or Closure, %s given.',
            $wheres
        );

        return $this->on($wheres, 'OR');
    }

    /**
     * onRaw
     *
     * @param  string|Clause  $condition
     * @param  mixed          ...$args
     *
     * @return  static
     */
    public function onRaw(Clause|string $condition, ...$args): static
    {
        if (!$this->on) {
            $this->on = $this->query->clause('ON', [], ' AND ');
        }

        if (is_string($condition) && $args !== []) {
            $condition = $this->query->format($condition, ...$args);
        }

        $this->on->append($condition);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->prefix . ' ' . $this->table . ' ' . $this->on;
    }

    /**
     * @return string|AsClause
     */
    public function getTable(): AsClause|string
    {
        return $this->table;
    }

    /**
     * Method to set property table
     *
     * @param  string|AsClause  $table
     *
     * @return  static  Return self to support chaining.
     */
    public function join(AsClause|string $table): static
    {
        $this->table = $table;

        return $this;
    }

    public function __call(string $name, array $args)
    {
        // Proxy to query
        $methods = [
            'raw',
            'format',
            'expr',
            'clause',
            'dateFormat',
            'nullDate',
            'escape',
            'quote',
            'quoteName',
        ];

        $method = $methods[strtolower($name)] ?? null;

        if ($method) {
            return $this->query->$method(...$args);
        }

        throw new BadMethodCallException('Call to undefined method: ' . $name . '()');
    }
}
