<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Scalars\Concern;

use Windwalker\Utilities\Arr;

/**
 * The ArrayContentTrait class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait ArrayAccessTrait
{
    /**
     * first
     *
     * @param  callable  $conditions
     *
     * @return  mixed
     */
    public function first(callable $conditions = null)
    {
        if ($conditions) {
            foreach ($this->storage as $key => $value) {
                if ($conditions($value, $key)) {
                    return $value;
                }
            }

            return null;
        }

        return $this->storage[array_key_first($this->storage)] ?? null;
    }

    /**
     * last
     *
     * @param  callable  $conditions
     *
     * @return  mixed
     */
    public function last(callable $conditions = null)
    {
        if ($conditions) {
            $prev = null;

            foreach ($this->storage as $key => $value) {
                if ($conditions($value, $key)) {
                    $prev = $value;
                }
            }

            return $prev;
        }

        return $this->storage[array_key_last($this->storage)] ?? null;
    }

    /**
     * flatten
     *
     * @param  string  $delimiter
     * @param  int     $depth
     * @param  string  $prefix
     *
     * @return  static
     *
     * @since  3.5.10
     */
    public function flatten(string $delimiter = '.', int $depth = 0, ?string $prefix = null)
    {
        return $this->newInstance(Arr::flatten($this->dump(), $delimiter, $depth, $prefix));
    }

    /**
     * collapse
     *
     * @param  bool  $keepKey
     *
     * @return  static
     *
     * @since  3.5.10
     */
    public function collapse(bool $keepKey = true)
    {
        return $this->newInstance(Arr::collapse($this->dump(), $keepKey));
    }

    /**
     * page
     *
     * @param  int  $page
     * @param  int  $limit
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function page(int $page, int $limit)
    {
        $new = clone $this;

        $new->storage = array_slice($new->storage, ($page - 1) * $limit, $limit);

        return $new;
    }
}
