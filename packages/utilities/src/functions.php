<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace {

    use Windwalker\Utilities\Arr;

    if (!function_exists('show')) {
        /**
         * Dump Array or Object as tree node. If send multiple params in this method, this function will batch print it.
         *
         * @param  mixed  $args  Array or Object to dump.
         *
         * @return  void
         * @since   2.0
         *
         */
        function show(...$args)
        {
            Arr::show(...$args);
        }
    }

    if (!function_exists('is_stringable')) {
        /**
         * is_stringable
         *
         * @param  mixed  $var
         *
         * @return  bool
         *
         * @since  3.5
         */
        function is_stringable($var): bool
        {
            if (is_array($var)) {
                return false;
            }

            if (is_object($var) && !$var instanceof Stringable && !method_exists($var, '__toString')) {
                return false;
            }

            if (is_resource($var)) {
                return false;
            }

            return true;
        }
    }

    if (!function_exists('is_json')) {
        /**
         * is_json
         *
         * @param  mixed  $string
         *
         * @return  bool
         *
         * @since  3.5.8
         */
        function is_json($string): bool
        {
            if (!is_string($string)) {
                return false;
            }

            json_decode($string);

            return json_last_error() === JSON_ERROR_NONE;
        }
    }

    include_once __DIR__ . '/serializer.php';
}

namespace Windwalker {

    use Closure;
    use Traversable;
    use Windwalker\Utilities\Compare\CompareHelper;
    use Windwalker\Utilities\Compare\WhereWrapper;
    use Windwalker\Utilities\Proxy\CachedCallable;
    use Windwalker\Utilities\Proxy\CallableProxy;
    use Windwalker\Utilities\Proxy\DisposableCallable;
    use Windwalker\Utilities\Wrapper\RawWrapper;
    use Windwalker\Utilities\Wrapper\ValueReference;
    use Windwalker\Utilities\Wrapper\WrapperInterface;

    /**
     * nope
     *
     * @return  Closure
     */
    function nope(): Closure
    {
        return fn($v) => $v;
    }

    /**
     * Do some operation after value get.
     *
     * @param  mixed     $value
     * @param  callable  $callable
     *
     * @return  mixed
     *
     * @since  3.5.1
     */
    function tap($value, callable $callable): mixed
    {
        $callable($value);

        return $value;
    }

    /**
     * Count NULL as 0 to workaround some code before php7.2
     *
     * @param  mixed  $value
     * @param  int    $mode
     *
     * @return  int
     *
     * @since  3.5.13
     */
    function count($value, $mode = COUNT_NORMAL): int
    {
        return $value !== null ? \count($value, $mode) : 0;
    }

    /**
     * iterator_keys
     *
     * @param  Traversable  $iterable
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    function iterator_keys(Traversable $iterable): array
    {
        return array_keys(iterator_to_array($iterable));
    }

    /**
     * where
     *
     * @param  mixed   $var1
     * @param  string  $operator
     * @param  mixed   $var2
     * @param  bool    $strict
     *
     * @return  WhereWrapper
     *
     * @since  __DEPLOY_VERSION__
     */
    function where($var1, string $operator, $var2, bool $strict = false): WhereWrapper
    {
        return new WhereWrapper($var1, $operator, $var2, $strict);
    }

    /**
     * value
     *
     * @param  mixed|Closure  $value
     * @param  mixed          ...$args
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    function value($value, ...$args): mixed
    {
        if ($value instanceof \WeakReference) {
            return $value->get();
        }

        if ($value instanceof WrapperInterface) {
            return $value(...$args);
        }

        return ($value instanceof Closure || $value instanceof CallableProxy)
            ? $value(...$args)
            : $value;
    }

    /**
     * unwrap
     *
     * @param  mixed  $value
     * @param  mixed  ...$args
     *
     * @return  mixed
     */
    function unwrap($value, ...$args): mixed
    {
        if ($value instanceof WrapperInterface) {
            return $value(...$args);
        }

        return $value;
    }

    /**
     * raw
     *
     * @param  mixed  $value
     *
     * @return  RawWrapper
     */
    function raw($value): RawWrapper
    {
        return new RawWrapper($value);
    }

    /**
     * ref
     *
     * @param  string       $path
     * @param  string|null  $delimiter
     *
     * @return  ValueReference
     *
     * @since  __DEPLOY_VERSION__
     */
    function ref(string $path, ?string $delimiter = null): ValueReference
    {
        return new ValueReference($path, $delimiter);
    }

    /**
     * dispose
     *
     * @param  callable  $callable
     *
     * @return  DisposableCallable
     */
    function disposable(callable $callable): DisposableCallable
    {
        return new DisposableCallable($callable);
    }

    /**
     * cachable
     *
     * @param  callable  $callable
     *
     * @return  CachedCallable
     */
    function cachable(callable $callable): CachedCallable
    {
        return new CachedCallable($callable);
    }

    function value_compare(mixed $a, mixed $b, ?string $operator = null): int|bool
    {
        return CompareHelper::compare($a, $b, $operator);
    }
}
