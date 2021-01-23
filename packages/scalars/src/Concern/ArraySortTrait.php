<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Scalars\Concern;

use Closure;
use Windwalker\Scalars\ArrayObject;
use Windwalker\Utilities\Arr;

use function Windwalker\tap;

/**
 * The ArraySortConcern class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait ArraySortTrait
{
    /**
     * Sort Dataset by key.
     *
     * @param  integer|callable  $flags  sort flags or user sort handler.
     *
     * @return  static  Support chaining.
     *
     * @since   3.5.2
     */
    public function sortKeys($flags = SORT_REGULAR): ArraySortTrait|static
    {
        $new = clone $this;

        if (is_callable($flags)) {
            uksort($new->storage, $flags);
        } else {
            ksort($new->storage, $flags);
        }

        return $new;
    }

    /**
     * Sort DataSet by key in reverse order
     *
     * @param  integer|callable  $flags  sort flags or user sort handler.
     *
     * @return  static  Support chaining.
     *
     * @since   3.5.2
     */
    public function sortKeysDesc($flags = SORT_REGULAR): static
    {
        return $this->sortKeys($flags)->reverse(true);
    }

    /**
     * Sort an array using a case insensitive "natural order" algorithm
     *
     * @return static
     */
    public function natureSortCaseInsensitive(): static
    {
        return tap(
            clone $this,
            static function (ArrayObject $new) {
                natcasesort($new->storage);
            }
        );
    }

    /**
     * Sort entries using a "natural order" algorithm
     *
     * @return static
     */
    public function natureSort(): ArraySortTrait|static
    {
        $new = clone $this;

        natsort($new->storage);

        return $new;
    }

    /**
     * Sort an array and maintain index association.
     *
     * @param  int|callable  $flags
     *
     * @return static
     */
    public function sort($flags = SORT_REGULAR): ArraySortTrait|static
    {
        $new = clone $this;

        if (is_callable($flags)) {
            uasort($new->storage, $flags);
        } else {
            asort($new->storage, $flags);
        }

        return $new;
    }

    /**
     * Sort an array in reverse order and maintain index association.
     *
     * @param  int  $flags
     *
     * @return static
     */
    public function sortDesc($flags = SORT_REGULAR): ArraySortTrait|static
    {
        $new = clone $this;

        arsort($new->storage, $flags);

        return $new;
    }

    /**
     * Sort by column or custom getter.
     *
     * @param  string|callable  $column
     * @param  int              $flags
     * @param  bool             $desc
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function sortBy(mixed $column, int $flags = SORT_REGULAR, bool $desc = false): static
    {
        $results = [];

        $getter = $column instanceof Closure ? $column : function ($item) use ($column) {
            return Arr::get($item, $column);
        };

        $handler = $desc ? 'arsort' : 'asort';

        foreach ($this->storage as $key => $value) {
            $results[$key] = $getter($value, $key);
        }

        $handler($results, $flags);

        foreach (array_keys($results) as $key) {
            $results[$key] = $this->storage[$key];
        }

        return $this->newInstance($results);
    }
}
