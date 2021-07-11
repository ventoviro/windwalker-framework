<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Handler;

use SessionHandlerInterface;

/**
 * Interface HandlerInterface
 */
interface HandlerInterface extends SessionHandlerInterface
{
    /**
     * isSupported
     *
     * @return  boolean
     */
    public static function isSupported(): bool;
}
