<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Stub;

use Windwalker\Http\Output\HttpCompressor;

/**
 * The StubHttpCompressor class.
 *
 * @since  3.0
 */
class StubHttpCompressor extends HttpCompressor
{
    /**
     * checkConnectionAlive
     *
     * @return  bool
     */
    public function checkConnectionAlive(): bool
    {
        return true;
    }

    /**
     * checkHeadersSent
     *
     * @return  bool
     */
    public function checkHeadersSent(): bool
    {
        return false;
    }
}
