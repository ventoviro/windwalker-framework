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
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\TypeCast;

/**
 * The ArrayModifyTrait class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait ArrayCreationTrait
{
    /**
     * combine
     *
     * @param  array|static  $values
     *
     * @return  static
     *
     * @since  3.5
     */
    public function combine(mixed $values): static
    {
        return $this->newInstance(array_combine($this->storage, TypeCast::toArray($values)));
    }

    /**
     * diff
     *
     * @param  array[]|static[]  ...$args
     *
     * @return  static
     *
     * @since  3.5
     */
    public function diff(...$args): static
    {
        return $this->newInstance(array_diff($this->storage, ...static::mapUnwrap($args)));
    }

    /**
     * diffKeys
     *
     * @param  array[]|static[]  ...$args
     *
     * @return  static
     *
     * @since  3.5
     */
    public function diffKeys(...$args): static
    {
        return $this->newInstance(array_diff_key($this->storage, ...static::mapUnwrap($args)));
    }

    /**
     * fillWith
     *
     * @param  int    $start
     * @param  int    $num
     * @param  mixed  $value
     *
     * @return  static
     *
     * @since  3.5
     */
    public static function fillWith(int $start, int $num, mixed $value): static
    {
        return static::wrap(array_fill($start, $num, $value));
    }

    /**
     * fillKeys
     *
     * @param  array  $keys
     * @param  mixed  $value
     *
     * @return  static
     *
     * @since  3.5
     */
    public function fillKeys(array $keys, mixed $value): static
    {
        return $this->newInstance(array_fill_keys($keys, $value));
    }

    /**
     * flip
     *
     * @return  static
     *
     * @since  3.5
     */
    public function flip(): static
    {
        return $this->newInstance(array_flip($this->storage));
    }

    /**
     * intersect
     *
     * @param  array[]|static[]  ...$args
     *
     * @return  static
     *
     * @since  3.5
     */
    public function intersect(...$args): static
    {
        return $this->newInstance(array_intersect($this->storage, ...static::mapUnwrap($args)));
    }

    /**
     * intersectKey
     *
     * @param  array[]|static[]  ...$args
     *
     * @return  static
     *
     * @since  3.5
     */
    public function intersectKey(...$args): static
    {
        return $this->newInstance(array_intersect_key($this->storage, ...static::mapUnwrap($args)));
    }

    /**
     * merge
     *
     * @param  array[]|static[]  ...$args
     *
     * @return  static
     *
     * @since  3.5
     */
    public function merge(...$args): static
    {
        return $this->newInstance(array_merge($this->storage, ...static::mapUnwrap($args)));
    }

    /**
     * mergeRecursive
     *
     * @param  array[]|static[]  ...$args
     *
     * @return  static
     *
     * @since  3.5
     */
    public function mergeRecursive(...$args): static
    {
        $args = array_map(
            static function ($arg) {
                return $arg instanceof ArrayObject ? $arg->dump() : $arg;
            },
            $args
        );

        return $this->newInstance(Arr::mergeRecursive($this->storage, ...$args));
    }

    /**
     * countValues
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function countValues(): static
    {
        return $this->group()->map('count');
    }

    /**
     * rand
     *
     * @param  int  $num
     *
     * @return  static
     *
     * @since  3.5
     */
    public function rand(int $num = 1): static
    {
        return $this->newInstance(array_rand($this->storage, $num));
    }

    /**
     * crossJoin
     *
     * @param  mixed  ...$args
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function crossJoin(...$args): static
    {
        return $this->newInstance(
            Arr::crossJoin(
                $this->storage,
                ...array_map(
                    [TypeCast::class, 'toArray'],
                    $args
                )
            )
        );
    }

    /**
     * range
     *
     * @param  mixed      $start
     * @param  mixed      $end
     * @param  int|float  $step
     *
     * @return  static
     *
     * @since  3.5
     */
    public static function range(mixed $start, mixed $end, int|float $step = 1): static
    {
        return static::wrap(range($start, $end, $step));
    }
}
