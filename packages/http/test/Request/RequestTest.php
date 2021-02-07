<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Request;

use PHPUnit\Framework\TestCase;
use Windwalker\Http\Request\Request;
use Windwalker\Uri\Uri;

/**
 * Test class of Request
 *
 * @since 2.1
 */
class RequestTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var Request
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
        $this->instance = new Request();
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
     * Method to test getHeaders().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\Request::getHeaders
     */
    public function testGetHeaders()
    {
        self::assertEquals([], $this->instance->getHeaders());

        $request = $this->instance->withUri(new Uri('http://windwalker.io/flower/sakura'));

        self::assertEquals(['Host' => ['windwalker.io']], $request->getHeaders());
    }

    /**
     * Method to test getHeader().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\Request::getHeader
     */
    public function testGetHeader()
    {
        self::assertEquals([], $this->instance->getHeader('host'));

        $request = $this->instance->withUri(new Uri('http://windwalker.io/flower/sakura'));

        self::assertEquals(['windwalker.io'], $request->getHeader('host'));
    }

    /**
     * Method to test hasHeader().
     *
     * @return  void
     *
     * @covers \Windwalker\Http\Request\Request::hasHeader
     */
    public function testHasHeader()
    {
        $request = new Request('http://example.com/foo', 'GET');

        self::assertTrue($request->hasHeader('host'));
        self::assertTrue($request->hasHeader('Host'));
        self::assertFalse($request->hasHeader('X-Foo'));
    }
}
