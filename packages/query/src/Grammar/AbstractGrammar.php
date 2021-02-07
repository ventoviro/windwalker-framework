<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\DefaultConnection;
use Windwalker\Query\Escaper;
use Windwalker\Query\Query;
use Windwalker\Utilities\Assert\ArgumentsAssert;
use Windwalker\Utilities\TypeCast;
use Windwalker\Utilities\Wrapper\RawWrapper;
use Windwalker\Utilities\Wrapper\WrapperInterface;

use function Windwalker\value;

/**
 * The AbstractGrammar class.
 */
abstract class AbstractGrammar
{
    /**
     * @var string
     */
    protected static string $name = '';

    /**
     * @var array
     */
    public static array $nameQuote = ['"', '"'];

    /**
     * @var string
     */
    public static string $nullDate = '0000-00-00 00:00:00';

    /**
     * @var string
     */
    public static string $dateFormat = 'Y-m-d H:i:s';

    /**
     * @var Escaper
     */
    protected Escaper $escaper;

    /**
     * create
     *
     * @param  string|null  $name
     *
     * @return  static
     */
    public static function create(?string $name = null): BaseGrammar|static
    {
        if ($name === null) {
            if ($grammar = DefaultConnection::getGrammar()) {
                return $grammar;
            }
        }

        $class = sprintf('%s\%sGrammar', __NAMESPACE__, ucfirst((string) $name));

        if (!class_exists($class)) {
            $class = BaseGrammar::class;
        }

        return new $class();
    }

    /**
     * Method to get property Name
     *
     * @return  string
     */
    public static function getName(): string
    {
        return static::$name;
    }

    /**
     * Compile Query object to SQL string.
     *
     * @param  string  $type
     * @param  Query   $query
     *
     * @return  string
     */
    public function compile(string $type, Query $query): string
    {
        if ($type === '') {
            throw new \InvalidArgumentException('Type shouldn\'t be empty string');
        }

        $method = 'compile' . ucfirst($type);

        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException(
                sprintf(
                    '%s dose not support "%s" compiled',
                    static::class,
                    $type
                )
            );
        }

        $sql = $this->$method($query);

        if ($suffix = $query->getSuffix()) {
            $sql .= ' ' . $suffix;
        }

        return $sql;
    }

    public function compileSelect(Query $query): string
    {
        $sql['select'] = (string) $query->getSelect();

        if ($form = $query->getFrom()) {
            $sql['from'] = $form;
        }

        if ($join = $query->getJoin()) {
            $sql['join'] = $join;
        }

        if ($where = $query->getWhere()) {
            $sql['where'] = $where;
        }

        if ($having = $query->getHaving()) {
            $sql['having'] = $having;
        }

        if ($group = $query->getGroup()) {
            $sql['group'] = $group;
        }

        if ($union = $query->getUnion()) {
            // Is full union
            if (!$query->getSelect()) {
                $union->setName('()');

                unset($sql['group']);
            }

            $sql['union'] = (string) $union;
        }

        // Only order and limit can after union
        if ($order = $query->getOrder()) {
            $sql['order'] = $order;
        }

        $sql = $this->compileLimit($query, $sql);

        return implode(' ', $sql);
    }

    public function compileInsert(Query $query): string
    {
        $sql['insert'] = $query->getInsert();

        if ($set = $query->getSet()) {
            $sql['set'] = $set;
        } else {
            if ($columns = $query->getColumns()) {
                $sql['columns'] = $columns;
            }

            if ($values = $query->getValues()) {
                $sql['values'] = $values;
            }
        }

        return trim(implode(' ', $sql));
    }

    public function compileUpdate(Query $query): string
    {
        $sql['update'] = $query->getUpdate();

        if ($join = $query->getJoin()) {
            $sql['join'] = $join;
        }

        if ($set = $query->getSet()) {
            $sql['set'] = $set;
        }

        if ($where = $query->getWhere()) {
            $sql['where'] = $where;
        }

        return trim(implode(' ', $sql));
    }

    public function compileDelete(Query $query): string
    {
        $sql['delete'] = $query->getDelete();

        if ($form = $query->getFrom()) {
            $sql['from'] = $form;
        }

        if ($join = $query->getJoin()) {
            $sql['join'] = $join;
        }

        if ($where = $query->getWhere()) {
            $sql['where'] = $where;
        }

        return trim(implode(' ', $sql));
    }

    public function compileCustom(Query $query): string
    {
        return (string) $query->getSql();
    }

    public static function quoteNameMultiple(mixed $name, bool $ignoreDot = false): mixed
    {
        if ($name instanceof RawWrapper) {
            return value($name);
        }

        if ($name instanceof Clause) {
            return $name->setElements(static::quoteNameMultiple($name->elements, $ignoreDot));
        }

        if (is_iterable($name)) {
            foreach ($name as &$n) {
                $n = static::quoteNameMultiple($n, $ignoreDot);
            }

            return $name;
        }

        return static::quoteName((string) $name, $ignoreDot);
    }

    public static function quoteName(string $name, bool $ignoreDot = false): string
    {
        if ($name === '*') {
            return $name;
        }

        if (stripos($name, ' as ') !== false) {
            [$name, $alias] = preg_split('/ as /i', $name);

            return static::quoteName($name) . ' AS ' . static::quoteName($alias);
        }

        if (str_contains($name, '.') && !$ignoreDot) {
            $name = trim($name, '.');

            return implode(
                '.',
                array_map(
                    [static::class, 'quoteName'],
                    explode('.', $name)
                )
            );
        }

        return static::$nameQuote[0] . $name . static::$nameQuote[1];
    }

    /**
     * compileLimit
     *
     * @param  Query  $query
     * @param  array  $sql
     *
     * @return  array
     */
    public function compileLimit(Query $query, array $sql): array
    {
        $limit  = $query->getLimit();
        $offset = $query->getOffset();

        if ($limit !== null) {
            $limitOffset = new Clause('LIMIT', (int) $limit, ', ');

            if ($offset !== null) {
                $limitOffset->prepend($offset);
            }

            $sql['limit'] = $limitOffset;
        }

        return $sql;
    }

    /**
     * If no connection set, we escape it with default function.
     *
     * @param string $text
     *
     * @return  string
     */
    public function localEscape(string $text): string
    {
        $text = str_replace("'", "''", $text);

        return addcslashes($text, "\000\n\r\\\032");
    }

    public static function nullDate(): string
    {
        return static::$nullDate;
    }

    public static function dateFormat(): string
    {
        return static::$dateFormat;
    }

    /**
     * clause
     *
     * @param  string  $name
     * @param  mixed   $elements
     * @param  string  $glue
     *
     * @return  Clause
     */
    public static function clause(string $name = '', $elements = [], string $glue = ' '): Clause
    {
        return new Clause($name, $elements, $glue);
    }

    /**
     * build
     *
     * @param  string|null  ...$args
     *
     * @return  Clause
     */
    public static function build(?string ...$args): Clause
    {
        $clause = static::clause('', [], ' ');

        foreach ($args as $arg) {
            $clause->append($arg);
        }

        return $clause;
    }

    public static function buildConfig(array $elements, string $separator = '='): string
    {
        $elements = array_filter(TypeCast::mapAs($elements, 'string'), 'strlen');
        $items = [];

        foreach ($elements as $key => $element) {
            $items[] = $key . $separator . $element;
        }

        return implode(' ', $items);
    }

    /**
     * @param  Escaper  $escaper
     *
     * @return  static  Return self to support chaining.
     */
    public function setEscaper(Escaper $escaper): static
    {
        $this->escaper = $escaper;

        return $this;
    }

    public function createQuery(): Query
    {
        return new Query($this->escaper, $this);
    }

    // /**
    //  * dropTable
    //  *
    //  * @param  string  $table
    //  * @param  bool    $ifExists
    //  * @param  mixed   ...$options
    //  *
    //  * @return  Clause
    //  */
    // public function dropTable(string $table, bool $ifExists = false, ...$options): Clause
    // {
    //     return static::build(
    //         'DROP TABLE',
    //         $ifExists ? 'IF EXISTS' : null,
    //         self::quoteName($table),
    //         ...$options
    //     );
    // }
    //
    // /**
    //  * dropTable
    //  *
    //  * @param  string  $table
    //  * @param  bool    $ifExists
    //  * @param  mixed   ...$options
    //  *
    //  * @return  Clause
    //  */
    // public function dropView(string $table, bool $ifExists = false, ...$options): Clause
    // {
    //     return static::build(
    //         'DROP VIEW',
    //         $ifExists ? 'IF EXISTS' : null,
    //         self::quoteName($table),
    //         ...$options
    //     );
    // }
}
