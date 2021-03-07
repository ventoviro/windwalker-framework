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
 * The Authorisation class.
 *
 * @since  3.0
 */
class Authorisation implements AuthorisationInterface
{
    /**
     * Property policies.
     *
     * @var  PolicyInterface[]
     */
    protected array $policies = [];

    /**
     * authorise
     *
     * @param  string  $policy
     * @param  mixed   $user
     * @param  mixed   ...$args
     *
     * @return  boolean
     */
    public function authorise(string $policy, mixed $user, mixed ...$args): bool
    {
        if (!$this->hasPolicy($policy)) {
            throw new \OutOfBoundsException(sprintf('Policy "%s" not exists', $policy));
        }

        return $this->getPolicy($policy)->authorise($user, ...$args);
    }

    /**
     * addPolicy
     *
     * @param  string                    $name
     * @param  callable|PolicyInterface  $handler
     *
     * @return  static
     */
    public function addPolicy(string $name, callable|PolicyInterface $handler): static
    {
        if (is_callable($handler)) {
            $handler = new CallbackPolicy($handler);
        }

        if (!$handler instanceof PolicyInterface) {
            throw new \InvalidArgumentException('Not a valid policy, please give a callable or PolicyInterface');
        }

        $this->policies[$name] = $handler;

        return $this;
    }

    /**
     * getPolicy
     *
     * @param   string $name
     *
     * @return  ?PolicyInterface
     */
    public function getPolicy(string $name): ?PolicyInterface
    {
        return $this->policies[$name] ?? null;
    }

    /**
     * registerPolicy
     *
     * @param PolicyProviderInterface $policy
     *
     * @return  static
     */
    public function registerPolicyProvider(PolicyProviderInterface $policy): static
    {
        $policy->register($this);

        return $this;
    }

    /**
     * hasPolicy
     *
     * @param   string $name
     *
     * @return  boolean
     */
    public function hasPolicy(string $name): bool
    {
        return isset($this->policies[$name]);
    }

    /**
     * Method to get property Policies
     *
     * @return  PolicyInterface[]
     */
    public function getPolicies(): array
    {
        return $this->policies;
    }

    /**
     * Method to set property policies
     *
     * @param   PolicyInterface[] $policies
     *
     * @return  static  Return self to support chaining.
     */
    public function setPolicies(array $policies): static
    {
        foreach ($policies as $name => $policy) {
            $this->addPolicy($name, $policy);
        }

        return $this;
    }
}
