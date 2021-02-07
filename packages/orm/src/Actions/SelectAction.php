<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Actions;

use Exception;
use Traversable;
use Windwalker\Data\Collection;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Event\ItemFetchedEvent;
use Windwalker\ORM\ORM;
use Windwalker\Query\Clause\AsClause;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * The SelectAction class.
 *
 * Query methods.
 *
 * @method  $this  select(...$columns)
 * @method  $this  selectAs(mixed $column, ?string $alias = null, bool $isColumn = true)
 * @method  $this  selectRaw(mixed $column, ...$args)
 * @method  $this  from(mixed $tables, ?string $alias = null)
 * @method  $this  join(string $type, mixed $table, ?string $alias, ...$on)
 * @method  $this  where(mixed $column, mixed ...$args)
 * @method  $this  whereRaw(Clause|string $string, ...$args)
 * @method  $this  orWhere(array|\Closure $wheres)
 * @method  void  having($column, ...$args)
 * @method  $this  havingRaw($string, ...$args)
 * @method  $this  orHaving(array|\Closure $wheres)
 * @method  $this  order(mixed $column, ?string $dir = null)
 * @method  $this  group(...$columns)
 * @method  $this  limit(?int $limit)
 * @method  $this  offset(?int $offset)
 * @method  string  escape(mixed $value)
 * @method  mixed  quote(mixed $value)
 * @method  mixed  quoteName(mixed $name)
 * @method  $this  suffix(array|string $suffix, ...$args)
 * @method  $this  rowLock(string $for = 'UPDATE', ?string $do = null)
 * @method  $this  forUpdate(?string $do = null)
 * @method  $this  forShare(?string $do = null)
 * @method  void  nullDate()
 * @method  void  getDateFormat()
 * @method  RawWrapper  raw(string $string, ...$args)
 * @method  string  format(string $format, ...$args)
 * @method  $this  pipe(callable $callback, ...$args): static
 * @method  $this  tap(callable $callback, ...$args): static
 * @method  $this  pipeIf(bool $bool, callable $callback, ...$args)
 * @method  $this  tapIf(bool $bool, callable $callback, ...$args)
 * @method  $this  bindParam($key = null, &$value = null, $dataType = null, int $length = 0, $driverOptions = null)
 * @method  $this  bind($key = null, $value = null, $dataType = null)
 * @method  ?array &getBounded($key = null)
 * @method  $this  resetBounded()
 * @method  $this  unbind(mixed $keys)
 *
 * Query magic methods.
 *
 * @method $this leftJoin($table, ?string $alias, ...$on)
 * @method $this rightJoin($table, ?string $alias, ...$on)
 * @method $this outerJoin($table, ?string $alias, ...$on)
 * @method $this innerJoin($table, ?string $alias, ...$on)
 * @method $this whereIn($column, array $values)
 * @method $this whereNotIn($column, array $values)
 * @method $this whereBetween($column, $start, $end)
 * @method $this whereNotBetween($column, $start, $end)
 * @method $this whereLike($column, string $search)
 * @method $this whereNotLike($column, string $search)
 * @method $this havingIn($column, array $values)
 * @method $this havingNotIn($column, array $values)
 * @method $this havingBetween($column, $start, $end)
 * @method $this havingNotBetween($column, $start, $end)
 * @method $this havingLike($column, string $search)
 * @method $this havingNotLike($column, string $search)
 */
class SelectAction implements \IteratorAggregate
{
    /**
     * @var ORM
     */
    protected ORM $orm;

    /**
     * @var Query
     */
    protected Query $query;

    protected ?string $groupDivider = null;

    /**
     * SelectAction constructor.
     *
     * @param  ORM  $orm
     */
    public function __construct(ORM $orm)
    {
        $this->orm = $orm;

        $this->resetQuery();
    }

    public function autoSelections(string $divider = '.'): static
    {
        /** @var array<int, AsClause> $tables */
        $tables = array_values(Arr::collapse($this->query->getAllTables()));

        $db = $this->orm->getDb();

        foreach ($tables as $i => $clause) {
            $tbm = $db->getTable(
                $this->query->stripNameQuote($clause->getValue())
            );

            $cols = $tbm->getColumnNames();

            foreach ($cols as $col) {
                $alias = $this->query->stripNameQuote($clause->getAlias());

                if ($i === 0) {
                    $as = $col;
                } else {
                    $as = $alias . $divider . $col;
                }

                $this->query->selectRaw(
                    '%n AS %r',
                    $alias . '.' . $col,
                    $this->query->quoteName($as, true)
                );
            }
        }

        return $this;
    }

    public function groupByDivider(string $divider = '.'): static
    {
        $this->groupDivider = $divider;

        return $this;
    }

    protected function groupItem(Collection $item): Collection
    {
        foreach ($item as $k => $value) {
            if (str_contains($k, $this->groupDivider)) {
                [$prefix, $key] = explode($this->groupDivider, $k, 2);

                $item[$prefix] ??= new Collection();

                $item[$prefix][$key] = $value;

                unset($item[$k]);
            }
        }

        return $item;
    }

    /**
     * createQuery
     *
     * @return  Query
     */
    public function resetQuery(): Query
    {
        return $this->query = $this->orm->getDb()->createQuery();
    }

    /**
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }

    public function __call(string $name, array $args)
    {
        // Load
        $methods = [
            'get',
            'all',
        ];

        if (in_array(strtolower($name), $methods)) {
            return $this->registerGroupEvent(
                $this->orm->getDb()->prepare($this->query)
            )
                ->$name(...$args);
        }

        $this->query->$name(...$args);

        return $this;

        // throw new \BadMethodCallException(
        //     sprintf(
        //         'Call to undefined method %s() of %s',
        //         $name,
        //         static::class
        //     )
        // );
    }

    protected function registerGroupEvent(StatementInterface $stmt): StatementInterface
    {
        if ($this->groupDivider === null) {
            return $stmt;
        }

        return $stmt->on(
            ItemFetchedEvent::class,
            function (ItemFetchedEvent $event) {
                $item = $this->groupItem($event->getItem());
                $event->setItem($item);
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): StatementInterface
    {
        return $this->registerGroupEvent(
            $this->orm->getDb()->prepare($this->query)
        );
    }
}
