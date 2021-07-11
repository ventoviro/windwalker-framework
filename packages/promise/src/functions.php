<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise;

use Closure;
use Generator;
use JetBrains\PhpStorm\Pure;
use Throwable;
use Windwalker\Promise\Helper\ReturnPromiseInterface;

use function is_object;
use function method_exists;

if (!function_exists('\Windwalker\Promise\resolve')) {
    /**
     * resolve
     *
     * @param  mixed|PromiseInterface  $promiseOrValue
     *
     * @return  ExtendedPromiseInterface
     * @throws Throwable
     */
    function resolve($promiseOrValue = null): ExtendedPromiseInterface
    {
        return Promise::resolved($promiseOrValue);
    }
}

if (!function_exists('\Windwalker\Promise\reject')) {
    /**
     * reject
     *
     * @param  mixed|PromiseInterface  $promiseOrValue
     *
     * @return  ExtendedPromiseInterface
     * @throws Throwable
     */
    function reject($promiseOrValue = null): ExtendedPromiseInterface
    {
        return Promise::rejected($promiseOrValue);
    }
}

if (!function_exists('\Windwalker\Promise\is_thenable')) {
    /**
     * is_thenable
     *
     * @param  mixed  $value
     *
     * @return  bool
     */
    #[Pure]
    function is_thenable(
        mixed $value
    ): bool {
        return is_object($value) && method_exists($value, 'then');
    }
}

if (!function_exists('\Windwalker\Promise\asyncable')) {
    /**
     * async
     *
     * @param  callable  $callable
     *
     * @return  Closure|ReturnPromiseInterface
     */
    function asyncable(callable $callable): Closure|ReturnPromiseInterface
    {
        return static function (...$args) use ($callable): ExtendedPromiseInterface {
            return async(
                static function () use ($callable, $args) {
                    return $callable(...$args);
                }
            );
        };
    }
}

if (!function_exists('\Windwalker\Promise\async')) {
    /**
     * async
     *
     * @param  callable  $callable
     *
     * @return  ExtendedPromiseInterface
     */
    function async(callable $callable): ExtendedPromiseInterface
    {
        return new Promise(
            static function ($resolve, $reject) use ($callable) {
                try {
                    $resolve($callable());
                } catch (Throwable $e) {
                    $reject($e);
                }
            }
        );
    }
}

if (!function_exists('\Windwalker\Promise\await')) {
    /**
     * await
     *
     * @param  PromiseInterface  $promise
     *
     * @return  mixed
     */
    function await(PromiseInterface $promise): mixed
    {
        return $promise->wait();
    }
}

if (!function_exists('\Windwalker\Promise\coroutine')) {
    /**
     * Run a coroutine context and auto catch yield values as promise then wait then.
     *
     * Every yield values will block process and wait previous call. Similar to ES async/await.
     *
     * Example:
     *
     * ```php
     * $res3 = \Windwalker\coroutine(function () {
     *     $res1 = yield anAsyncFunctionReturnPromise();
     *     $res2 = yield anAsyncFunctionReturnPromise();
     *
     *     return $res1 . $res2; // <-- return as new promise
     * })
     *     ->then(...)
     *     ->wait();
     * ```
     *
     * @param  callable  $callback
     *
     * @return  ExtendedPromiseInterface
     *
     * @throws Throwable
     */
    function coroutine(callable $callback): ExtendedPromiseInterface
    {
        return new Promise(
            static function ($resolve) use ($callback) {
                \Windwalker\go(
                    static function () use ($resolve, $callback) {
                        /** @var Generator $generator */
                        $generator = $callback();

                        $value = $generator->current();

                        while ($generator->valid()) {
                            $value = $generator->send(Promise::resolved($value)->wait());
                        }

                        $resolve($generator->getReturn());
                    }
                );
            }
        );
    }
}

if (!function_exists('\Windwalker\Promise\coroutineable')) {
    /**
     * Make a callable be coroutine but keep origin interfaces.
     *
     * Example:
     *
     * ```php
     * $run = \Windwalker\coroutineable([$foo, 'run']);
     *
     * $run($a, $b)->then(...);
     * ```
     *
     * @param  callable  $callback
     *
     * @return  Closure|ReturnPromiseInterface
     *
     * @throws Throwable
     * @see coroutine()
     *
     */
    function coroutineable(callable $callback): Closure|ReturnPromiseInterface
    {
        return static function (...$args) use ($callback): ExtendedPromiseInterface {
            return coroutine(
                static function () use ($callback, $args) {
                    return $callback(...$args);
                }
            );
        };
    }
}
