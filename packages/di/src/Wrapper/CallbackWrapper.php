<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\DI\Wrapper;

use Windwalker\Utilities\Wrapper\WrapperInterface;

/**
 * The CallbackWrapper class.
 */
class CallbackWrapper implements WrapperInterface
{
    /**
     * @var callable
     */
    public $callable;

    /**
     * CallbackWrapper constructor.
     *
     * @param  callable     $callable
     * @param  object|null  $context
     * @param  int          $options
     */
    public function __construct(callable $callable, public ?object $context, public int $options = 0)
    {
        $this->callable = $callable;
    }

    /**
     * Get wrapped value.
     *
     * @param  mixed|null  $src
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __invoke(mixed $src = null): callable
    {
        return $this->callable;
    }
}
