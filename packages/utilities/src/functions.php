<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace {

    use JetBrains\PhpStorm\Pure;
    use Windwalker\Utilities\Arr;
    use Windwalker\Utilities\Env;

    if (!function_exists('env')) {
        /**
         * Get ENV var.
         *
         * @param string $name
         * @param mixed  $default
         *
         * @return  string|null
         */
        function env(string $name, $default = null): ?string
        {
            return Env::get($name, $default);
        }
    }

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
        #[Pure]
        function is_stringable(mixed $var): bool
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
        #[Pure]
        function is_json(mixed $string): bool
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
    use JetBrains\PhpStorm\Pure;
    use Traversable;
    use Windwalker\Utilities\Compare\CompareHelper;
    use Windwalker\Utilities\Compare\WhereWrapper;
    use Windwalker\Utilities\Proxy\CachedCallable;
    use Windwalker\Utilities\Proxy\CallableProxy;
    use Windwalker\Utilities\Proxy\DisposableCallable;
    use Windwalker\Utilities\Serial;
    use Windwalker\Utilities\Wrapper\RawWrapper;
    use Windwalker\Utilities\Wrapper\ValueReference;
    use Windwalker\Utilities\Wrapper\WrapperInterface;

    if (!function_exists('\Windwalker\nope')) {
        /**
         * nope
         *
         * @return  Closure
         */
        #[Pure]
        function nope(): Closure
        {
            return static fn($v) => $v;
        }
    }

    if (!function_exists('\Windwalker\tap')) {
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
        function tap(mixed $value, callable $callable): mixed
        {
            $callable($value);

            return $value;
        }
    }

    if (!function_exists('\Windwalker\count')) {
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
        function count(mixed $value, int $mode = COUNT_NORMAL): int
        {
            if ($value instanceof \Traversable) {
                return iterator_count($value);
            }

            return $value !== null ? \count($value, $mode) : 0;
        }
    }

    if (!function_exists('\Windwalker\uid')) {
        /**
         * uid
         *
         * @param  string  $prefix
         * @param  bool    $timebase
         *
         * @return  string
         *
         * @throws \Exception
         */
        function uid(string $prefix = '', bool $timebase = false): string
        {
            if ($timebase) {
                [$b, $a] = explode(' ', $c = microtime(), 2);

                $c = $a . substr($b, 2);

                return $prefix . base_convert($c, 10, 12) . bin2hex(random_bytes(4));
            }

            return $prefix . bin2hex(random_bytes(12));
        }
    }

    if (!function_exists('\Windwalker\serial')) {
        /**
         * To get a auto increment serial number. The sequence can be string name or and object.
         *
         * @param  string|object  $sequence
         *
         * @return  int
         */
        function serial(string|object $sequence = 'default'): int
        {
            return Serial::get($sequence);
        }
    }

    if (!function_exists('\Windwalker\iterator_keys')) {
        /**
         * iterator_keys
         *
         * @param  Traversable  $iterable
         *
         * @return  array
         *
         * @since  __DEPLOY_VERSION__
         */
        #[Pure]
        function iterator_keys(Traversable $iterable): array
        {
            return array_keys(iterator_to_array($iterable));
        }
    }

    if (!function_exists('\Windwalker\where')) {
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
        #[Pure]
        function where(mixed $var1, string $operator, mixed $var2, bool $strict = false): WhereWrapper
        {
            return new WhereWrapper($var1, $operator, $var2, $strict);
        }
    }

    if (!function_exists('\Windwalker\value')) {
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
        function value(mixed $value, mixed ...$args): mixed
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
    }

    if (!function_exists('\Windwalker\unwrap')) {
        /**
         * unwrap
         *
         * @param  mixed  $value
         * @param  mixed  ...$args
         *
         * @return  mixed
         */
        function unwrap(mixed $value, ...$args): mixed
        {
            if ($value instanceof WrapperInterface) {
                return $value(...$args);
            }

            return $value;
        }
    }

    if (!function_exists('\Windwalker\raw')) {
        /**
         * raw
         *
         * @param  mixed  $value
         *
         * @return  RawWrapper
         */
        #[Pure]
        function raw(mixed $value): RawWrapper
        {
            return new RawWrapper($value);
        }
    }

    if (!function_exists('\Windwalker\ref')) {
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
        #[Pure]
        function ref(string $path, ?string $delimiter = '.'): ValueReference
        {
            return new ValueReference($path, $delimiter);
        }
    }

    if (!function_exists('\Windwalker\disposable')) {
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
    }

    if (!function_exists('\Windwalker\cachable')) {
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
    }

    if (!function_exists('\Windwalker\value_compare')) {
        /**
         * value_compare
         *
         * @param  mixed        $a
         * @param  mixed        $b
         * @param  string|null  $operator
         *
         * @return  int|bool
         */
        function value_compare(mixed $a, mixed $b, ?string $operator = null): int|bool
        {
            return CompareHelper::compare($a, $b, $operator);
        }
    }
}
