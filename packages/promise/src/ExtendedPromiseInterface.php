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
 * Interface ExtendedPromiseInterface
 *
 * @since  __DEPLOY_VERSION__
 */
interface ExtendedPromiseInterface extends PromiseInterface
{
    /**
     * @param  callable|null  $onFulfilled
     *
     * @return static
     */
    public function done(?callable $onFulfilled = null): static;

    /**
     * @param  callable  $onRejected
     *
     * @return static
     */
    public function catch(?callable $onRejected): static;

    /**
     * @param  callable  $onFulfilledOrRejected
     *
     * @return static
     */
    public function finally(?callable $onFulfilledOrRejected): static;
}
