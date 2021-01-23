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
use Windwalker\Utilities\TypeCast;

/**
 * Trait ArrayModifyTrait
 *
 * @since  __DEPLOY_VERSION__
 */
trait ArrayModifyTrait
{
    /**
     * pad
     *
     * @param  int    $size
     * @param  mixed  $value
     *
     * @return  static
     *
     * @since  3.5
     */
    public function pad(int $size, mixed $value): static
    {
        return $this->newInstance(array_pad($this->storage, $size, $value));
    }

    /**
     * leftPad
     *
     * @param  int    $size
     * @param  mixed  $value
     *
     * @return  static
     *
     * @since  3.5
     */
    public function leftPad(int $size, mixed $value): static
    {
        return $this->pad(-$size, $value);
    }

    /**
     * leftPad
     *
     * @param  int    $size
     * @param  mixed  $value
     *
     * @return  static
     *
     * @since  3.5
     */
    public function rightPad(int $size, mixed $value): static
    {
        return $this->pad($size, $value);
    }

    /**
     * pop
     *
     * @return  mixed
     *
     * @since  3.5
     */
    public function pop(): mixed
    {
        return array_pop($this->storage);
    }

    /**
     * shift
     *
     * @return  mixed
     *
     * @since  3.5
     */
    public function shift(): mixed
    {
        return array_shift($this->storage);
    }

    /**
     * push
     *
     * @param  mixed  ...$value
     *
     * @return  int
     *
     * @since  3.5
     */
    public function push(...$value): int
    {
        return array_push($this->storage, ...$value);
    }

    /**
     * unshift
     *
     * @param  mixed  ...$value
     *
     * @return  int
     *
     * @since  3.5
     */
    public function unshift(...$value): int
    {
        return array_unshift($this->storage, ...$value);
    }

    /**
     * concat
     *
     * @param  mixed  ...$args
     *
     * @return  static
     *
     * @since  3.5.13
     */
    public function append(...$args): static
    {
        $new = $this->storage;
        array_push($new, ...$args);

        return $this->newInstance($new);
    }

    /**
     * concatStart
     *
     * @param  mixed  ...$args
     *
     * @return  static
     *
     * @since  3.5.13
     */
    public function prepend(...$args): static
    {
        $new = $this->storage;
        array_unshift($new, ...$args);

        return $this->newInstance($new);
    }

    /**
     * removeEnd
     *
     * @param  int  $num
     *
     * @return  static
     *
     * @since  3.5.13
     */
    public function removeLast($num = 1): static
    {
        return (clone $this)->splice(0, -$num);
    }

    /**
     * removeStart
     *
     * @param  int  $num
     *
     * @return  $this
     *
     * @since  3.5.13
     */
    public function removeFirst($num = 1): static
    {
        return (clone $this)->splice($num);
    }

    /**
     * replace
     *
     * @param  array[]|static[]  ...$args
     *
     * @return  static
     *
     * @since  3.5
     */
    public function replace(...$args): static
    {
        return $this->newInstance(array_replace($this->storage, ...static::mapUnwrap($args)));
    }

    /**
     * replaceRecursive
     *
     * @param  array[]|static[]  ...$args
     *
     * @return  static
     *
     * @since  3.5
     */
    public function replaceRecursive(...$args): static
    {
        return $this->newInstance(array_replace_recursive($this->storage, ...static::mapUnwrap($args)));
    }

    /**
     * reverse
     *
     * @param  bool  $preserveKeys
     *
     * @return  static
     *
     * @since  3.5
     */
    public function reverse(bool $preserveKeys = false): static
    {
        return $this->newInstance(array_reverse($this->storage, $preserveKeys));
    }

    /**
     * slice
     *
     * @param  int       $offset
     * @param  int|null  $length
     * @param  bool      $preserveKeys
     *
     * @return  static
     *
     * @since  3.5
     */
    public function slice(int $offset, ?int $length = null, bool $preserveKeys = false): static
    {
        return $this->newInstance(array_slice($this->storage, ...func_get_args()));
    }

    /**
     * slice
     *
     * @param  int       $offset
     * @param  int|null  $length
     * @param  mixed     $replacement
     *
     * @return  static
     *
     * @since  3.5
     */
    public function splice(int $offset, ?int $length = null, $replacement = null): static
    {
        return $this->newInstance(array_splice($this->storage, ...func_get_args()));
    }

    /**
     * insertAfter
     *
     * @param  int    $key
     * @param  array  $args
     *
     * @return  static
     *
     * @since  3.5
     */
    public function insertAfter(int $key, ...$args): static|ArrayModifyTrait
    {
        $new = clone $this;

        $new->splice($key + 1, 0, $args);

        return $new;
    }

    /**
     * insertBefore
     *
     * @param  int    $key
     * @param  mixed  $value
     *
     * @return  static
     *
     * @since  3.5
     */
    public function insertBefore(int $key, mixed $value): static|ArrayModifyTrait
    {
        $new = clone $this;

        $new->splice($key, 0, $value);

        return $new;
    }

    /**
     * only
     *
     * @param  array  $fields
     *
     *
     * @return  static
     */
    public function only(array $fields): static
    {
        return $this->newInstance(Arr::only($this->storage, $fields));
    }

    /**
     * except
     *
     * @param  array  $fields
     *
     * @return  static
     *
     * @since  3.5.13
     */
    public function except(array $fields): static
    {
        return $this->newInstance(Arr::except($this->storage, $fields));
    }

    /**
     * shuffle
     *
     * @return  static
     *
     * @since  3.5
     */
    public function shuffle(): static
    {
        $new = $this->storage;

        shuffle($new);

        return $this->newInstance($new);
    }

    /**
     * takeout
     *
     * @param  string  $key
     * @param  mixed   $default
     * @param  string  $delimiter
     *
     * @return  mixed
     */
    public function takeout(mixed $key, mixed $default = null, string $delimiter = '.'): mixed
    {
        return Arr::takeout($this->storage, $key, $default, $delimiter);
    }

    /**
     * chunk
     *
     * @param  int   $size
     * @param  bool  $preserveKeys
     *
     * @return  static
     */
    public function chunk(int $size, bool $preserveKeys = false): static
    {
        return $this->newInstance(array_chunk($this->storage, $size, $preserveKeys))
            ->wrapAll();
    }

    /**
     * groupBy
     *
     * @param  string  $column
     * @param  int     $type
     *
     * @return  static
     *
     * @since  3.5.3
     */
    public function group(?string $column = null, int $type = Arr::GROUP_TYPE_ARRAY): static
    {
        return $this->newInstance(Arr::group($this->dump(), $column, $type));
    }

    /**
     * keyBy
     *
     * @param  string  $field
     *
     * @return  static
     */
    public function keyBy(string $field): static
    {
        return $this->newInstance(Arr::group($this->dump(), $field, Arr::GROUP_TYPE_KEY_BY));
    }

    /**
     * union
     *
     * @param  array[]  ...$args
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function union(...$args): static|ArrayModifyTrait
    {
        $new = clone $this;

        foreach ($args as $arg) {
            $new->storage += TypeCast::toArray($arg);
        }

        return $new;
    }
}
