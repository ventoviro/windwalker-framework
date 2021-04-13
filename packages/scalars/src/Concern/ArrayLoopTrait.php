<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Scalars\Concern;

use Windwalker\Scalars\ArrayObject;
use Windwalker\Scalars\ScalarsFactory;
use Windwalker\Scalars\StringObject;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Context\Loop;
use Windwalker\Utilities\TypeCast;

/**
 * The ArrayLoopConcern class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait ArrayLoopTrait
{
    /**
     * @var  Loop|null
     */
    protected static $currentLoop = null;

    /**
     * reduce
     *
     * @param  callable  $callable
     * @param  mixed     $initial
     *
     * @return  ArrayObject|StringObject|mixed
     *
     * @since  3.5
     */
    public function reduce(callable $callable, $initial = null): mixed
    {
        $result = array_reduce($this->storage, $callable, $initial);

        return ScalarsFactory::fromNative($result);
    }

    /**
     * walk
     *
     * @param  callable  $callable
     * @param  mixed     $userdata
     *
     * @return  static
     *
     * @since  3.5
     */
    public function walk(callable $callable, $userdata = null): ArrayLoopTrait|static
    {
        $new = clone $this;

        array_walk($new->storage, $callable, $userdata);

        return $new;
    }

    /**
     * walkRecursive
     *
     * @param  callable  $callable
     * @param  mixed     $userdata
     *
     * @return  static
     *
     * @since  3.5
     */
    public function walkRecursive(callable $callable, $userdata = null): ArrayLoopTrait|static
    {
        $new = clone $this;

        array_walk_recursive($new->storage, $callable, $userdata);

        return $new;
    }

    /**
     * each
     *
     * @param  callable  $callback
     *
     * @return  static
     */
    public function each(callable $callback): static
    {
        $i = 0;

        static::$currentLoop = new Loop(count($this), $this->storage, static::$currentLoop);

        foreach ($this as $key => $value) {
            $callback($value, $key, static::$currentLoop->loop($i, $key));

            if (static::$currentLoop->isStop()) {
                break;
            }

            $i++;
        }

        static::$currentLoop = static::$currentLoop->parent();

        return $this;
    }

    /**
     * find
     *
     * @param  callable  $callback
     * @param  bool      $keepKey
     * @param  int       $offset
     * @param  int       $limit
     *
     * @return static
     */
    public function find(callable $callback, $keepKey = false, $offset = null, $limit = null): static
    {
        return $this->newInstance(Arr::find($this->storage, $callback, $keepKey, $offset, $limit));
    }

    /**
     * query
     *
     * @param  array|callable  $queries
     * @param  bool            $strict
     * @param  bool            $keepKey
     *
     * @return  static
     *
     * @since  3.5.8
     */
    public function query($queries = [], bool $strict = false, bool $keepKey = false): static
    {
        return $this->newInstance(Arr::query($this->storage, $queries, $strict, $keepKey));
    }

    /**
     * filter
     *
     * @param  callable  $callback
     *
     * @return  static
     */
    public function filter(callable $callback = null): static
    {
        return $this->find($callback, true);
    }

    /**
     * findFirst
     *
     * @param  callable  $callback
     *
     * @return  mixed
     */
    public function findFirst(callable $callback = null): mixed
    {
        return Arr::findFirst($this->storage, $callback);
    }

    /**
     * reject
     *
     * @param  callable  $callback
     * @param  bool      $keepKey
     *
     * @return  static
     */
    public function reject(callable $callback, $keepKey = false): static
    {
        return $this->newInstance(Arr::reject($this->storage, $callback, $keepKey));
    }

    /**
     * partition
     *
     * @param  callable  $callback
     * @param  bool      $keepKey
     *
     * @return  static[]
     */
    public function partition(callable $callback, $keepKey = false): array
    {
        $true  = [];
        $false = [];

        if (is_string($callback)) {
            $callback = static function ($value) use ($callback) {
                return $callback($value);
            };
        }

        foreach ($this->storage as $key => $value) {
            if ($callback($value, $key)) {
                $true[$key] = $value;
            } else {
                $false[$key] = $value;
            }
        }

        if (!$keepKey) {
            $true  = array_values($true);
            $false = array_values($false);
        }

        return [
            $this->newInstance($true),
            $this->newInstance($false),
        ];
    }

    /**
     * Mapping all elements.
     *
     * @param  callable  $callback
     * @param  array     ...$args
     *
     * @return  static  Support chaining.
     *
     * @since   2.0.9
     */
    public function map(callable $callback, ...$args): static
    {
        // Keep keys same as origin
        return $this->newInstance(array_map($callback, $this->storage, ...$args));
    }

    /**
     * mapRecursive
     *
     * @param  callable  $callback
     * @param  bool      $useKeys
     * @param  bool      $loopIterable
     *
     * @return  static
     *
     * @since  3.5.8
     */
    public function mapRecursive(callable $callback, bool $useKeys = false, bool $loopIterable = false): static
    {
        return $this->map(
            function ($value, $key = null) use ($useKeys, $callback, $loopIterable) {
                if (is_array($value)) {
                    return Arr::mapRecursive($value, $callback, $useKeys, $loopIterable);
                }

                if (is_iterable($value)) {
                    return Arr::mapRecursive(iterator_to_array($value), $callback, $useKeys, $loopIterable);
                }

                if (is_array($value) || $value instanceof static) {
                    return $this->newInstance($value)->mapRecursive($callback, $useKeys, $loopIterable);
                }

                return $callback($value, $key);
            },
            ...($useKeys ? $this->keys() : [])
        );
    }

    /**
     * mapWithKeys
     *
     * @param  callable  $handler
     * @param  int       $groupType
     *
     * @return  static
     *
     * @since  3.5.12
     */
    public function mapWithKeys(callable $handler, int $groupType = self::GROUP_TYPE_KEY_BY): static
    {
        return $this->newInstance(Arr::mapWithKeys($this->storage, $handler, $groupType));
    }

    /**
     * flatMap
     *
     * @param  callable  $callback
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function flatMap(callable $callback): static
    {
        return $this->newInstance(Arr::flatMap($this->storage, $callback));
    }

    /**
     * mapAs
     *
     * @param  string  $class
     *
     * @return  static
     *
     * @since  3.5.13
     */
    public function mapAs(string $class): static
    {
        return $this->newInstance(TypeCast::mapAs($this->storage, $class));
    }
}
