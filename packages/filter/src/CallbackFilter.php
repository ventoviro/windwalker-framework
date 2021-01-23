<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter;

/**
 * The CallbackFilter class.
 */
class CallbackFilter extends AbstractCallbackFilter
{
    /**
     * @var callable
     */
    protected $handler;

    /**
     * CallbackFilter constructor.
     *
     * @param  callable  $handler
     */
    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return callable
     */
    public function getHandler(): callable
    {
        return $this->handler;
    }

    /**
     * @param  callable  $handler
     *
     * @return  static  Return self to support chaining.
     */
    public function setHandler(callable $handler): static
    {
        $this->handler = $handler;

        return $this;
    }
}
