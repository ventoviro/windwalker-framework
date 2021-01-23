<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Authorisation;

/**
 * The AuthorisationInterface class.
 *
 * @since  3.0
 */
interface AuthorisationInterface
{
    /**
     * authorise
     *
     * @param  string  $policy
     * @param  mixed   $user
     * @param  mixed   $data
     *
     * @return  bool
     */
    public function authorise(string $policy, mixed $user, mixed $data = null): bool;

    /**
     * addPolicy
     *
     * @param  string    $name
     * @param  callable  $handler
     *
     * @return  static
     */
    public function addPolicy(string $name, callable $handler): static;

    /**
     * registerPolicy
     *
     * @param PolicyProviderInterface $policy
     *
     * @return  static
     */
    public function registerPolicyProvider(PolicyProviderInterface $policy): static;
}
