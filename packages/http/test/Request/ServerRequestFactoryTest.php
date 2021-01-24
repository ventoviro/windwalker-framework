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
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Http\Request\ServerRequestFactory;
use Windwalker\Http\UploadedFile;
use Windwalker\Stream\Stream;

/**
 * Test class of ServerRequestFactory
 *
 * @since 3.0
 */
class ServerRequestFactoryTest extends TestCase
{
    /**
     * Method to test create().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequestFactory::createFromGlobals
     */
    public function testCreate()
    {
        $request = ServerRequestFactory::createFromGlobals();

        self::assertTrue($request instanceof ServerRequest);
    }

    /**
     * Method to test prepareServers().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequestFactory::prepareServers
     */
    public function testPrepareServers()
    {
        $bak = ServerRequestFactory::$apacheRequestHeaders;

        ServerRequestFactory::$apacheRequestHeaders = [$this, 'apacheRequestHeaders'];

        $server = [];

        $server = ServerRequestFactory::prepareServers($server);

        self::assertEquals('foo', $server['HTTP_AUTHORIZATION']);

        // Test no auth
        ServerRequestFactory::$apacheRequestHeaders = [$this, 'apacheRequestHeadersEmpty'];

        $server = [];

        $server = ServerRequestFactory::prepareServers($server);

        self::assertTrue(empty($server['HTTP_AUTHORIZATION']));

        ServerRequestFactory::$apacheRequestHeaders = $bak;
    }

    /**
     * apacheRequestHeaders
     *
     * @return  array
     */
    public function apacheRequestHeaders(): array
    {
        return [
            'authorization' => 'foo',
        ];
    }

    /**
     * apacheRequestHeadersEmpty
     *
     * @return  array
     */
    public function apacheRequestHeadersEmpty(): array
    {
        return [];
    }

    /**
     * Method to test prepareFiles().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequestFactory::prepareFiles
     */
    public function testPrepareFiles()
    {
        $files = [
            [
                'tmp_name' => 'php://temp',
                'size' => 123,
                'error' => 0,
                'name' => 'foo_name',
                'type' => 'foo_type',
            ],
        ];

        $files = ServerRequestFactory::prepareFiles($files);

        self::assertInstanceOf(UploadedFile::class, $files[0]);
        self::assertInstanceOf(Stream::class, $files[0]->getStream());
        self::assertEquals('foo_name', $files[0]->getClientFilename());
        self::assertEquals(0, $files[0]->getError());

        $files = [
            'first' => [
                'tmp_name' => [
                    'foo' => 'php://temp',
                    'bar' => 'php://temp',
                ],
                'size' => [
                    'foo' => 123,
                    'bar' => 321,
                ],
                'error' => [
                    'foo' => 1,
                    'bar' => 2,
                ],
                'name' => [
                    'foo' => 'foo_name',
                    'bar' => 'bar_name',
                ],
                'type' => [
                    'foo' => 'foo_type',
                    'bar' => 'bar_type',
                ],
            ],
            'second' => [
                'tmp_name' => 'php://temp',
                'size' => 456,
                'error' => 0,
                'name' => 'second_name',
                'type' => 'second_type',
            ],
        ];

        $files = ServerRequestFactory::prepareFiles($files);

        self::assertTrue($files['first']['foo'] instanceof UploadedFile);
        self::assertTrue($files['first']['bar'] instanceof UploadedFile);
        self::assertTrue($files['second'] instanceof UploadedFile);
    }

    /**
     * Method to test prepareHeaders().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequestFactory::prepareHeaders
     */
    public function testPrepareHeaders()
    {
        $headers = [
            'HTTP_X_FOO' => 'foo',
            'HTTP_X_BAR' => 'bar',
            'HTTP_X_FLOWER' => 'Sakura',
            'CONTENT_YOO' => 'baz',
            'CONTENT_BIRD' => 'fly',
        ];

        $expected = [
            'x-foo' => 'foo',
            'x-bar' => 'bar',
            'x-flower' => 'Sakura',
            'content-yoo' => 'baz',
            'content-bird' => 'fly',
        ];

        self::assertEquals($expected, ServerRequestFactory::prepareHeaders($headers));
    }

    /**
     * Method to test prepareUri().
     *
     * @param  array   $servers
     * @param  array   $headers
     * @param  string  $expected
     *
     * @covers       \Windwalker\Http\Request\ServerRequestFactory::prepareUri
     *
     * @dataProvider prepareUri_Provider
     */
    public function testPrepareUri($servers, $headers, $expected)
    {
        $uri = ServerRequestFactory::prepareUri($servers, $headers);

        self::assertEquals($expected, $uri->__toString());
    }

    /**
     * prepareUri_Provider
     *
     * @return  array
     */
    public function prepareUri_Provider(): array
    {
        return [
            '#apache-normal' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                ],
                [],
                'http://example.com:8080/foo/bar?a=b&c=d',
                __LINE__,
            ],
            '#apache-fragment' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar#test?a=b&c=d',
                ],
                [],
                'http://example.com:8080/foo/bar?a=b&c=d#test',
                __LINE__,
            ],
            '#apache-https' => [
                [
                    'HTTPS' => 'on',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                ],
                [],
                'https://example.com:8080/foo/bar?a=b&c=d',
                __LINE__,
            ],
            '#apache-x-forwarded' => [
                [
                    'HTTPS' => '',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                ],
                [
                    'x-forwarded-proto' => 'https',
                ],
                'https://example.com:8080/foo/bar?a=b&c=d',
                __LINE__,
            ],
            '#apache-header-host' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => '',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                ],
                [
                    'host' => 'example.com',
                ],
                // Will never get port because host in header is a cache
                'http://example.com/foo/bar?a=b&c=d',
                __LINE__,
            ],
            '#apache-ipv6' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => '[2001:db8:a0b:12f0::1]',
                    'SERVER_ADDR' => '2001:db8:a0b:12f0::1',
                    'SERVER_PORT' => 8080,
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                ],
                [],
                'http://[2001:db8:a0b:12f0::1]:8080/foo/bar?a=b&c=d',
                __LINE__,
            ],
            '#iis-rewritten' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'IIS_WasUrlRewritten' => '1',
                    'UNENCODED_URL' => 'flower/sakura',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                ],
                [],
                'http://example.com:8080/flower/sakura?a=b&c=d',
                __LINE__,
            ],
            '#iis-x-rewrite' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                    'HTTP_X_REWRITE_URL' => '/flower/sakura?a=wrong',
                ],
                [],
                'http://example.com:8080/flower/sakura?a=b&c=d',
                __LINE__,
            ],
            '#iis-origin-url' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                    'HTTP_X_REWRITE_URL' => '/flower/sakura?a=wrong',
                    'HTTP_X_ORIGINAL_URL' => '/flower/olive?a=wrong',
                ],
                [],
                'http://example.com:8080/flower/olive?a=b&c=d',
                __LINE__,
            ],
            '#orig-path-info' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'ORIG_PATH_INFO' => '/flower/rose',
                ],
                [],
                'http://example.com:8080/flower/rose?a=b&c=d',
                __LINE__,
            ],
            '#no-path' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                ],
                [],
                'http://example.com:8080/?a=b&c=d',
                __LINE__,
            ],
        ];
    }

    /**
     * Method to test getHostAndPortFromHeaders().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequestFactory::getHostAndPortFromHeaders
     * @TODO   Implement testGetHostAndPortFromHeaders().
     */
    public function testGetHostAndPortFromHeaders()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getRequestUri().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequestFactory::getRequestUri
     * @TODO   Implement testGetRequestUri().
     */
    public function testGetRequestUri()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test stripQueryString().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Request\ServerRequestFactory::stripQueryString
     * @TODO   Implement testStripQueryString().
     */
    public function testStripQueryString()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
