<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Scheduler;

/**
 * The AsyncCursor class.
 */
class ScheduleCursor
{
    /**
     * @var mixed
     */
    protected $cursor;

    /**
     * AsyncCursor constructor.
     *
     * @param  mixed  $cursor
     */
    public function __construct($cursor = null)
    {
        $this->cursor = $cursor;
    }

    /**
     * Method to get property Cursor
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function get(): mixed
    {
        return $this->cursor;
    }
}
