<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Authorization;

/**
 * The PolicyInterface class.
 *
 * @since  3.0
 */
interface PolicyInterface
{
    /**
     * authorise
     *
     * @param  mixed  $user
     * @param  mixed  ...$args
     *
     * @return  boolean
     */
    public function authorise(mixed $user, mixed ...$args): bool;
}
