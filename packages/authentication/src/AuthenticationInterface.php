<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Authentication;

use Windwalker\Authentication\Method\MethodInterface;

/**
 * Interface AuthenticationInterface
 *
 * @since  3.0
 */
interface AuthenticationInterface
{
    /**
     * authenticate
     *
     * @param  array  $credential
     *
     * @return ResultSet
     */
    public function authenticate(array $credential): ResultSet;

    /**
     * addMethod
     *
     * @param  string           $name
     * @param  MethodInterface  $method
     *
     * @return  static
     */
    public function addMethod(string $name, MethodInterface $method): static;
}
