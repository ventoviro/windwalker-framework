<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Proxy;

/**
 * The CallbackProxy class.
 */
class CallableProxy
{
    /**
     * @var callable
     */
    protected $callable;

    /**
     * unwrap
     *
     * @param  callable  $callable
     *
     * @return  callable
     */
    public static function unwrap(callable $callable): callable
    {
        if ($callable instanceof self) {
            $callable = $callable->get(true);
        }

        return $callable;
    }

    /**
     * CallbackProxy constructor.
     *
     * @param  callable  $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * __invoke
     *
     * @param  mixed  ...$args
     *
     * @return  mixed|void
     */
    public function __invoke(...$args): mixed
    {
        $callback = $this->callable;

        return $callback(...$args);
    }

    /**
     * Method to get property Callable
     *
     * @param  bool  $recursive
     *
     * @return  callable
     *
     * @since  __DEPLOY_VERSION__
     */
    public function get(bool $recursive = false): callable
    {
        $callable = $this->callable;

        if ($recursive && $callable instanceof self) {
            $callable = $callable->get($recursive);
        }

        return $callable;
    }
}
