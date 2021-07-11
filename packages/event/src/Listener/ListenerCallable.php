<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event\Listener;

use Windwalker\Utilities\Proxy\TimesLimitedCallable;

/**
 * The ListenerItem class.
 */
class ListenerCallable extends TimesLimitedCallable
{
    /**
     * @var int
     */
    protected $priority;

    /**
     * @var bool
     */
    protected $once;

    /**
     * ListenerItem constructor.
     *
     * @param  callable  $callable
     * @param  int       $priority
     * @param  bool      $once
     */
    public function __construct(callable $callable, ?int $priority, bool $once)
    {
        $this->priority = $priority ?? ListenerPriority::NORMAL;
        $this->once = $once;

        parent::__construct($callable, $once ? 1 : null);
    }

    /**
     * is
     *
     * @param  callable  $callable
     *
     * @return  bool
     */
    public function sameWith(callable $callable): bool
    {
        if ($callable instanceof static) {
            $callable = $callable->get();
        }

        return $callable === $this->callable;
    }

    /**
     * Method to get property Priority
     *
     * @return  int
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Method to get property Once
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isOnce(): bool
    {
        return $this->once;
    }
}
