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
 * The ArrayMethod class.
 *
 * @since  2.0
 */
class ArrayMethod implements MethodInterface
{
    /**
     * Property users.
     *
     * @var  array
     */
    protected array $users = [];

    /**
     * Property verifyHandler.
     *
     * @var callable
     */
    protected $verifyHandler;

    /**
     * Class init.
     *
     * @param array $users
     */
    public function __construct(array $users = [])
    {
        $this->users = $users;
    }

    /**
     * authenticate
     *
     * @param  array  $credential
     *
     * @return AuthResult
     */
    public function authenticate(array $credential): AuthResult
    {
        $username = $credential['username'];
        $password = $credential['password'];

        if ((string) $username === '' || (string) $password === '') {
            return new AuthResult(AuthResult::EMPTY_CREDENTIAL, $credential);
        }

        if (!isset($this->users[$username])) {
            return new AuthResult(AuthResult::USER_NOT_FOUND, $credential);
        }

        $user = $this->users[$username];

        if (!isset($user['password'])) {
            return new AuthResult(AuthResult::INVALID_CREDENTIAL, $credential);
        }

        if (!$this->getVerifyHandler()($password, $user['password'])) {
            return new AuthResult(AuthResult::INVALID_CREDENTIAL, $credential);
        }

        return new AuthResult(AuthResult::SUCCESS, $credential);
    }

    /**
     * Method to get property VerifyHandler
     *
     * @return  callable
     */
    public function getVerifyHandler(): callable
    {
        return $this->verifyHandler ?? fn($password, $hash): bool => password_verify($password, $hash);
    }

    /**
     * Method to set property verifyHandler
     *
     * @param   callable $verifyHandler
     *
     * @return  static  Return self to support chaining.
     */
    public function setVerifyHandler(callable $verifyHandler): static
    {
        $this->verifyHandler = $verifyHandler;

        return $this;
    }

    /**
     * Method to get property Users
     *
     * @return  array
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * Method to set property users
     *
     * @param   array $users
     *
     * @return  static  Return self to support chaining.
     */
    public function setUsers(array $users): static
    {
        $this->users = $users;

        return $this;
    }
}
