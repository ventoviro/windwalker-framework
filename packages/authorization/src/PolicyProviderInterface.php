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
 * Interface PolicyInterface
 *
 * @since  3.0
 */
interface PolicyProviderInterface
{
    /**
     * register
     *
     * @param  AuthorizationInterface  $authorization
     *
     * @return  void
     */
    public function register(AuthorizationInterface $authorization): void;
}
