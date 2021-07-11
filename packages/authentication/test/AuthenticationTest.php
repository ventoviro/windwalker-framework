<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Authentication\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Authentication\Authentication;
use Windwalker\Authentication\AuthResult;
use Windwalker\Authentication\Credential;
use Windwalker\Authentication\Method\ArrayMethod;
use Windwalker\Authentication\Test\Mock\MockMethod;

/**
 * Test class of Authentication
 *
 * @since 2.0
 */
class AuthenticationTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var Authentication
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new Authentication();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    /**
     * Method to test authenticate().
     *
     * @return void
     *
     * @covers \Windwalker\Authentication\Authentication::authenticate
     */
    public function testAuthenticate()
    {
        $credential = ['username' => 'flower', 'password' => '1234'];

        $result = $this->instance->authenticate($credential);

        // Test if no method
        self::assertFalse($result->isSuccess());

        // Test for MockMethod
        $this->instance->addMethod('mock', new MockMethod());

        $result = $this->instance->authenticate($credential);

        // Test success
        self::assertTrue($result->isSuccess());

        self::assertEquals('mock', $result->getMatchedMethod());

        // Test invalid
        $credential['password'] = '4321';

        $result = $this->instance->authenticate($credential);

        self::assertFalse($result->isSuccess());

        $r = $result->getResults()['mock'];

        self::assertEquals(AuthResult::INVALID_CREDENTIAL, $r->getStatus());

        // Test No user
        $credential['username'] = 'sakura';

        $result = $this->instance->authenticate($credential);

        self::assertFalse($result->isSuccess());

        self::assertEquals(AuthResult::USER_NOT_FOUND, $result->getResults()['mock']->getStatus());
    }

    /**
     * Method to test addMethod().
     *
     * @return void
     *
     * @covers \Windwalker\Authentication\Authentication::addMethod
     */
    public function testAddAndGetMethod()
    {
        $this->instance->addMethod('flower', new MockMethod());

        self::assertInstanceOf(MockMethod::class, $this->instance->getMethod('flower'));
    }

    /**
     * Method to test removeMethod().
     *
     * @return void
     *
     * @covers \Windwalker\Authentication\Authentication::removeMethod
     */
    public function testRemoveMethod()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getResults().
     *
     * @return void
     */
    public function testGetResults()
    {
        $this->instance->addMethod('a', new ArrayMethod());
        $this->instance->addMethod('b', new MockMethod());

        $credential = ['username' => 'flower', 'password' => '1234'];

        $result = $this->instance->authenticate($credential);
        $result = array_map(fn(AuthResult $r) => $r->getStatus(), $result->getResults());

        self::assertEquals(
            [
                'a' => AuthResult::USER_NOT_FOUND,
                'b' => AuthResult::SUCCESS,
            ],
            $result
        );
    }
}
