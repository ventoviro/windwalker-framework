<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Authentication\Method;

use Windwalker\Authentication\AuthResult;

/**
 * Interface MethodInterface
 *
 * @since  2.0
 */
interface MethodInterface
{
    /**
     * authenticate
     *
     * @param  array  $credential
     *
     * @return AuthResult
     */
    public function authenticate(array $credential): AuthResult;
}
