<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Authorisation;

/**
 * The CallbackPolicy class.
 *
 * @since  3.0
 */
class CallbackPolicy implements PolicyInterface
{
    /**
     * Property handler.
     *
     * @var  callable
     */
    protected $handler;

    /**
     * CallbackPolicy constructor.
     *
     * @param callable $handler
     */
    public function __construct(callable $handler)
    {
        $this->setHandler($handler);
    }

    /**
     * authorise
     *
     * @param  mixed  $user
     * @param  mixed  ...$args
     *
     * @return  bool
     */
    public function authorise(mixed $user, mixed ...$args): bool
    {
        return $this->getHandler()($user, ...$args);
    }

    /**
     * Method to get property Handler
     *
     * @return  callable
     */
    public function getHandler(): callable
    {
        return $this->handler;
    }

    /**
     * Method to set property handler
     *
     * @param   callable $handler
     *
     * @return  static  Return self to support chaining.
     */
    public function setHandler(callable $handler): static
    {
        if (!is_callable($handler)) {
            throw new \InvalidArgumentException('Handler should be a valid callback');
        }

        $this->handler = $handler;

        return $this;
    }
}
