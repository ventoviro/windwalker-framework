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
use ReflectionException;
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Http\UploadedFile;
use Windwalker\Http\Uri;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * Test class of ServerRequest
 *
 * @since 2.1
 */
class ServerRequestTest extends TestCase
{
    protected ?ServerRequest $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new ServerRequest();
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
     * testConstruct
     *
     * @return  void
     * @throws ReflectionException
     */
    public function testConstruct()
    {
        $server = [
            'foo' => 'bar',
            'baz' => 'bat',
        ];

        $server['server'] = true;

        $files = [
            'files' => new UploadedFile('php://temp', 0),
        ];

        $uri     = new Uri('http://example.com');
        $method  = 'POST';
        $headers = [
            'Host' => ['example.com'],
        ];

        $request = new ServerRequest(
            $server,
            $files,
            $uri,
            $method,
            'php://memory',
            $headers
        );

        $this->assertEquals($server, $request->getServerParams());
        $this->assertEquals($files, $request->getUploadedFiles());

        $this->assertSame($uri, $request->getUri());
        $this->assertEquals($method, $request->getMethod());
        $this->assertEquals($headers, $request->getHeaders());

        $body   = $request->getBody();
        $stream = ReflectAccessor::getValue($body, 'stream');

        $this->assertEquals('php://memory', $stream);
    }

    /**
     * Method to test getServerParams().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::getServerParams
     * @TODO   Implement testGetServerParams().
     */
    public function testGetServerParams()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getCookieParams().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::getCookieParams
     * @TODO   Implement testGetCookieParams().
     */
    public function testGetCookieParams()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test withCookieParams().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::withCookieParams
     * @TODO   Implement testWithCookieParams().
     */
    public function testWithCookieParams()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getQueryParams().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::getQueryParams
     * @TODO   Implement testGetQueryParams().
     */
    public function testGetQueryParams()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test withQueryParams().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::withQueryParams
     * @TODO   Implement testWithQueryParams().
     */
    public function testWithQueryParams()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getUploadedFiles().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::getUploadedFiles
     * @TODO   Implement testGetUploadedFiles().
     */
    public function testGetUploadedFiles()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test withUploadedFiles().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::withUploadedFiles
     * @TODO   Implement testWithUploadedFiles().
     */
    public function testWithUploadedFiles()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getParsedBody().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::getParsedBody
     * @TODO   Implement testGetParsedBody().
     */
    public function testGetParsedBody()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test withParsedBody().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::withParsedBody
     * @TODO   Implement testWithParsedBody().
     */
    public function testWithParsedBody()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getAttributes().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::getAttributes
     * @TODO   Implement testGetAttributes().
     */
    public function testGetAttributes()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getAttribute().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::getAttribute
     * @TODO   Implement testGetAttribute().
     */
    public function testGetAttribute()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test withAttribute().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::withAttribute
     * @TODO   Implement testWithAttribute().
     */
    public function testWithAttribute()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test withoutAttribute().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequest::withoutAttribute
     * @TODO   Implement testWithoutAttribute().
     */
    public function testWithoutAttribute()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
