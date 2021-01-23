<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise;

/**
 * Interface PromiseInterface
 *
 * @since  __DEPLOY_VERSION__
 */
interface PromiseInterface
{
    public const PENDING = 'pending';

    public const FULFILLED = 'fulfilled';

    public const REJECTED = 'rejected';

    /**
     * Appends fulfillment and rejection handlers to the promise, and returns
     * a new promise resolving to the return value of the called handler.
     *
     * @see https://promisesaplus.com/#the-then-method
     *
     * @param  callable|mixed  $onFulfilled  Invoked when the promise fulfills.
     * @param  callable|mixed  $onRejected   Invoked when the promise is rejected.
     *
     * @return static
     */
    public function then(
        $onFulfilled = null,
        $onRejected = null
    ): static;

    /**
     * Get the state of the promise ("pending", "rejected", or "fulfilled").
     *
     * The three states can be checked against the constants defined on
     * PromiseInterface: PENDING, FULFILLED, and REJECTED.
     *
     * @return string
     */
    public function getState(): string;

    /**
     * Resolve the promise with the given value.
     *
     * @param  mixed  $value
     */
    public function resolve($value): void;

    /**
     * Reject the promise with the given reason.
     *
     * @param  mixed  $reason
     */
    public function reject($reason): void;

    /**
     * Waits until the promise completes if possible.
     *
     * If the promise cannot be waited on, then the promise will be rejected.
     *
     * @return mixed
     * @throws \LogicException if the promise has no wait function or if the
     *                         promise does not settle after waiting.
     */
    public function wait(): mixed;
}
