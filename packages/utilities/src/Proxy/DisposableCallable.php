<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Proxy;

use function Windwalker\tap;

/**
 * The DisposableCallable class.
 */
class DisposableCallable extends CallableProxy
{
    /**
     * @var bool
     */
    protected bool $called = false;

    /**
     * @inheritDoc
     */
    public function __invoke(...$args): mixed
    {
        if ($this->called) {
            return null;
        }

        return tap(
            parent::__invoke(...$args),
            function () {
                $this->called = true;
            }
        );
    }

    /**
     * Method to get property Called
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isCalled(): bool
    {
        return $this->called;
    }
}
