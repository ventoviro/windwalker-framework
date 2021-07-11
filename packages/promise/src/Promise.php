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
use ReflectionFunction;
use ReflectionMethod;
use Throwable;
use TypeError;
use Windwalker\Promise\Exception\UncaughtException;
use Windwalker\Promise\Scheduler\ScheduleCursor;
use Windwalker\Promise\Scheduler\ScheduleRunner;

use function is_array;
use function is_object;
use function Windwalker\nope;

/**
 * The Promise class.
 *
 * @since  __DEPLOY_VERSION__
 */
class Promise implements ExtendedPromiseInterface
{
    /**
     * @var string
     */
    protected string $state = self::PENDING;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var callable[]
     */
    protected array $handlers = [];

    /**
     * @var ScheduleCursor
     */
    protected ?ScheduleCursor $scheduleCursor = null;

    /**
     * create
     *
     * @param  callable|null  $resolver
     *
     * @return static
     */
    public static function create(?callable $resolver = null): static
    {
        $cb = $resolver;
        $resolver = null;

        return new static($cb);
    }

    /**
     * all
     *
     * @param  array  $values
     *
     * @return  ExtendedPromiseInterface
     */
    public static function all(array $values): ExtendedPromiseInterface
    {
        return new static(
            static function ($resolve, $reject) use ($values) {
                $count = count($values);
                $done = 0;

                foreach ($values as $i => $value) {
                    static::resolved($value)
                        ->then(
                            static function ($v) use (&$done, &$count, $resolve, $i, $values) {
                                $values[$i] = $v;
                                $done++;

                                if ($done !== $count) {
                                    return;
                                }

                                $resolve($values);
                            },
                            $reject
                        );
                }
            }
        );
    }

    /**
     * race
     *
     * @param  array  $values
     *
     * @return  ExtendedPromiseInterface
     */
    public static function race(array $values): ExtendedPromiseInterface
    {
        return new static(
            static function ($resolve, $reject) use ($values) {
                if ($values === []) {
                    $resolve();
                }

                foreach ($values as $i => $value) {
                    static::resolved($value)
                        ->then(
                            $resolve,
                            $reject
                        );
                }
            }
        );
    }

    /**
     * Promise constructor.
     *
     * @param  callable  $resolver
     */
    public function __construct(?callable $resolver = null)
    {
        // Explicitly overwrite arguments with null values before invoking
        // resolver function. This ensure that these arguments do not show up
        // in the stack trace in PHP 7+ only.
        $cb = $resolver;
        $resolver = null;

        $cb = $cb ?: static function () {
            //
        };

        $this->schedule(
            function () use ($cb) {
                $this->call($cb);
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function done(?callable $onFulfilled = null): static
    {
        return $this->then($onFulfilled);
    }

    /**
     * @inheritDoc
     */
    public function catch(?callable $onRejected): static
    {
        return $this->then(null, $onRejected);
    }

    /**
     * @inheritDoc
     */
    public function finally(?callable $onFulfilledOrRejected): static
    {
        return $this->then(
            function () use ($onFulfilledOrRejected) {
                $onFulfilledOrRejected();

                return $this->value;
            },
            function () use ($onFulfilledOrRejected) {
                $onFulfilledOrRejected();

                return static::rejected($this->value);
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function then($onFulfilled = null, $onRejected = null): static
    {
        $onFulfilled = is_callable($onFulfilled)
            ? $onFulfilled
            : nope();

        $onRejected = is_callable($onRejected)
            ? $onRejected
            : static function ($e) {
                throw new UncaughtException($e);
            };

        if ($this->getState() === static::PENDING) {
            $child = new static();

            $this->handlers[] = [
                $child,
                $onFulfilled,
                $onRejected,
            ];

            return $child;
        }

        $handler = $this->getState() === static::FULFILLED
            ? $onFulfilled
            : $onRejected;

        return new static(
            function ($resolve) use ($handler) {
                try {
                    $resolve($handler($this->value));
                } catch (UncaughtException $e) {
                    throw $e->getReason();
                }
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * resolved
     *
     * @param  mixed  $value
     *
     * @return  ExtendedPromiseInterface
     *
     * @throws Throwable
     * @since  __DEPLOY_VERSION__
     */
    public static function resolved($value = null): ExtendedPromiseInterface
    {
        return new static(
            static function (callable $resolve) use ($value) {
                $resolve($value);
            }
        );
    }

    /**
     * rejected
     *
     * @param  mixed  $value
     *
     * @return  ExtendedPromiseInterface
     *
     * @throws Throwable
     * @since  __DEPLOY_VERSION__
     */
    public static function rejected($value = null): ExtendedPromiseInterface
    {
        return new Promise(
            static function ($resolve, callable $reject) use ($value) {
                $reject($value);
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function resolve($value): void
    {
        $this->resolvePromise($this, $value);
    }

    /**
     * @inheritDoc
     */
    public function reject($reason): void
    {
        if ($reason === $this) {
            $this->reject(new TypeError('Unable to resolve self.'));

            return;
        }

        if ($this->getState() !== static::PENDING) {
            return;
        }

        $this->settle(static::REJECTED, $reason);
    }

    /**
     * @inheritDoc
     *
     * @throws Throwable
     */
    public function wait(): mixed
    {
        if ($this->getState() === static::PENDING) {
            $this->scheduleWait();
        }

        if ($this->value instanceof Throwable && $this->getState() === static::REJECTED) {
            throw $this->value;
        }

        return $this->value;
    }

    /**
     * Log the uncaught reject reason.
     *
     * @param  UncaughtException  $e
     *
     * @return  void
     */
    private function log(UncaughtException $e): void
    {
        //
    }

    /**
     * resolvePromise
     *
     * @param  PromiseInterface  $promise
     * @param  mixed             $value
     *
     * @return  PromiseInterface
     */
    private function resolvePromise(PromiseInterface $promise, mixed $value): PromiseInterface
    {
        if ($value === $promise) {
            $promise->reject(new TypeError('Unable to resolve self.'));

            return $promise;
        }

        if ($promise->getState() !== static::PENDING) {
            return $promise;
        }

        // If value is promise, start resolving after it resolved.
        if ($value instanceof PromiseInterface || is_thenable($value)) {
            $value->then(
                [$promise, 'resolve'],
                [$promise, 'reject']
            );

            return $promise;
        }

        $promise->settle(static::FULFILLED, $value);

        return $promise;
    }

    /**
     * runAsync
     *
     * @param  callable  $callback
     *
     * @return  void
     */
    protected function schedule(callable $callback): void
    {
        $this->scheduleCursor = ScheduleRunner::getInstance()->schedule($callback);
    }

    /**
     * waitAsync
     *
     * @return  void
     */
    protected function scheduleWait(): void
    {
        ScheduleRunner::getInstance()->wait($this->scheduleCursor);
    }

    /**
     * doneAsync
     *
     * @return  void
     */
    protected function scheduleDone(): void
    {
        ScheduleRunner::getInstance()->done($this->scheduleCursor);

        // Free cursor
        $this->scheduleCursor = null;
    }

    /**
     * settle
     *
     * @param  string  $state
     * @param  mixed   $value
     *
     * @return  void
     *
     * @throws Throwable
     * @since  __DEPLOY_VERSION__
     */
    private function settle(string $state, mixed $value): void
    {
        $handlers = $this->handlers;

        $this->state = $state;
        $this->value = $value;

        $this->scheduleDone();

        if ($handlers === [] && $state === static::REJECTED) {
            $this->log(new UncaughtException($value));

            return;
        }

        foreach ($handlers as $handler) {
            /** @var PromiseInterface $promise */
            [$promise, $onFulfilled, $onRejected] = $handler;

            $handler = $this->getState() === static::FULFILLED
                ? $onFulfilled
                : $onRejected;

            try {
                $promise->resolve($handler($value));
            } catch (UncaughtException $e) {
                $promise->reject($e->getReason());
            } catch (Throwable $e) {
                $promise->reject($e);
            }
        }
    }

    /**
     * Calling callback.
     *
     * This method is a clone of Reactphp/Promise
     *
     * @see https://github.com/reactphp/promise
     *
     * @param  callable|null  $cb
     *
     * @return  void
     * @throws Throwable
     */
    private function call(callable $cb): void
    {
        // Explicitly overwrite argument with null value. This ensure that this
        // argument does not show up in the stack trace in PHP 7+ only.
        $callback = $cb;
        $cb = null;

        // Use reflection to inspect number of arguments expected by this callback.
        // We did some careful benchmarking here: Using reflection to avoid unneeded
        // function arguments is actually faster than blindly passing them.
        // Also, this helps avoiding unnecessary function arguments in the call stack
        // if the callback creates an Exception (creating garbage cycles).
        if (is_array($callback)) {
            $ref = new ReflectionMethod($callback[0], $callback[1]);
        } elseif (is_object($callback) && !$callback instanceof Closure) {
            $ref = new ReflectionMethod($callback, '__invoke');
        } else {
            $ref = new ReflectionFunction($callback);
        }
        $args = $ref->getNumberOfParameters();

        try {
            if ($args === 0) {
                $callback();
            } else {
                // Keep references to this promise instance for the static resolve/reject functions.
                // By using static callbacks that are not bound to this instance
                // and passing the target promise instance by reference, we can
                // still execute its resolving logic and still clear this
                // reference when settling the promise. This helps avoiding
                // garbage cycles if any callback creates an Exception.
                // These assumptions are covered by the test suite, so if you ever feel like
                // refactoring this, go ahead, any alternative suggestions are welcome!
                $target =& $this;

                $callback(
                    static function ($value = null) use (&$target) {
                        if ($target !== null) {
                            $target->resolve($value);
                            $target = null;
                        }
                    },
                    static function ($reason = null) use (&$target) {
                        if ($target !== null) {
                            $target->reject($reason);
                            $target = null;
                        }
                    }
                );
            }
        } catch (Throwable $e) {
            $target = null;
            $this->reject($e);
        }
    }
}
