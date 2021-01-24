<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Test\Mock;

use SplPriorityQueue;
use SplStack;

/**
 * The Bar class.
 *
 * @since  2.0
 */
class Bar2
{
    /**
     * Property queue.
     *
     * @var  SplPriorityQueue
     */
    public $queue = null;

    /**
     * Property stack.
     *
     * @var  SplStack
     */
    public $stack = null;

    /**
     * Class init.
     *
     * @param  SplPriorityQueue  $queue
     * @param  SplStack          $stack
     */
    public function __construct(SplPriorityQueue $queue, SplStack $stack)
    {
        $this->queue = $queue;
        $this->stack = $stack;
    }
}
