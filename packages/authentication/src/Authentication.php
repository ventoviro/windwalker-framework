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
 * The Authentication class.
 *
 * @since  2.0
 */
class Authentication implements AuthenticationInterface
{
    /**
     * Property methods.
     *
     * @var  MethodInterface[]
     */
    protected array $methods = [];

    /**
     * Property credential.
     *
     * @var array
     */
    protected array $credential;

    /**
     * Authentication constructor.
     *
     * @param Method\MethodInterface[] $methods
     */
    public function __construct(array $methods = [])
    {
        $this->methods = $methods;
    }

    /**
     * authenticate
     *
     * @param  array  $credential
     *
     * @return ResultSet
     */
    public function authenticate(array $credential): ResultSet
    {
        $results = new ResultSet();

        foreach ($this->methods as $name => $method) {
            $results->addResult($name, $result = $method->authenticate($credential));

            if ($result->isSuccess()) {
                $results->matchedMethod = $name;

                return $results;
            }
        }

        return $results;
    }

    /**
     * addMethod
     *
     * @param  string           $name
     * @param  MethodInterface  $method
     *
     * @return  static
     */
    public function addMethod(string $name, MethodInterface $method): static
    {
        $this->methods[$name] = $method;

        return $this;
    }

    /**
     * getMethod
     *
     * @param string $name
     *
     * @return  MethodInterface
     */
    public function getMethod(string $name): ?MethodInterface
    {
        return $this->methods[$name] ?? null;
    }

    /**
     * removeMethod
     *
     * @param string $name
     *
     * @return  $this
     */
    public function removeMethod(string $name): static
    {
        unset($this->methods[$name]);

        return $this;
    }
}
