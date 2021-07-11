<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Request;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Test\Stub\StubRequest;
use Windwalker\Stream\Stream;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Uri\Uri;

/**
 * Test class of AbstractRequest
 *
 * @since 2.1
 */
class AbstractRequestTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?StubRequest $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new StubRequest();
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
     */
    public function testConstruct()
    {
        // Test no params
        $request = new StubRequest();

        self::assertInstanceOf(Uri::class, $request->getUri());
        self::assertEquals('', (string) $request->getUri());
        self::assertNull($request->getMethod());
        self::assertInstanceOf(Stream::class, $request->getBody());
        self::assertEquals('php://memory', $request->getBody()->getMetadata('uri'));
        self::assertEquals([], $request->getHeaders());

        // Test with params
        $uri = 'http://example.com/?foo=bar#baz';
        $method = 'post';
        $body = fopen($tmpfile = tempnam(sys_get_temp_dir(), 'windwalker'), 'wb+');
        $headers = [
            'X-Foo' => ['Flower', 'Sakura'],
            'Content-Type' => 'application/json',
        ];

        $request = new StubRequest($uri, $method, $body, $headers);

        self::assertInstanceOf(Uri::class, $request->getUri());
        self::assertEquals('http://example.com/?foo=bar#baz', (string) $request->getUri());
        self::assertEquals('POST', $request->getMethod());
        self::assertInstanceOf(Stream::class, $request->getBody());
        self::assertEquals($tmpfile, $request->getBody()->getMetadata('uri'));
        self::assertEquals(['Flower', 'Sakura'], $request->getHeader('x-foo'));
        self::assertEquals(['application/json'], $request->getHeader('content-type'));

        fclose($body);

        // Test with object params
        $uri = new Uri('http://example.com/flower/sakura?foo=bar#baz');
        $body = new Stream();
        $request = new StubRequest($uri, null, $body);

        self::assertSame($uri, $request->getUri());
        self::assertSame($body, $request->getBody());
    }

    /**
     * Method to test getRequestTarget().
     *
     * @return void
     */
    public function testWithAndGetRequestTarget()
    {
        self::assertEquals('/', $this->instance->getRequestTarget());

        $request = $this->instance->withUri(new Uri('http://example.com/flower/sakura?foo=bar#baz'));

        self::assertNotSame($request, $this->instance);
        self::assertEquals('/flower/sakura?foo=bar', (string) $request->getRequestTarget());

        $request = $request->withUri(new Uri('http://example.com'));

        self::assertEquals('/', (string) $request->getRequestTarget());

        $request = $request->withRequestTarget('*');

        self::assertEquals('*', $request->getRequestTarget());
    }

    /**
     * Method to test getMethod().
     *
     * @return void
     */
    public function testWithAndGetMethod()
    {
        self::assertNull($this->instance->getMethod());

        $request = $this->instance->withMethod('patch');

        self::assertNotSame($request, $this->instance);
        self::assertEquals('PATCH', $request->getMethod());

        self::assertExpectedException(
            function () use ($request) {
                $request->withMethod('FLY');
            },
            new InvalidArgumentException()
        );
    }

    /**
     * Method to test getUri().
     *
     * @return void
     */
    public function testWithAndGetUri()
    {
        self::assertInstanceOf(Uri::class, $this->instance->getUri());
        self::assertEquals('', (string) $this->instance->getUri());

        $request = $this->instance->withUri(new Uri('http://example.com/flower/sakura?foo=bar#baz'), true);

        self::assertNotSame($request, $this->instance);
        self::assertEquals('http://example.com/flower/sakura?foo=bar#baz', (string) $request->getUri());
        self::assertEquals([], $request->getHeader('host'));

        $request = $this->instance->withUri(new Uri('http://windwalker.io/flower/sakura?foo=bar#baz'));

        self::assertEquals('http://windwalker.io/flower/sakura?foo=bar#baz', (string) $request->getUri());
        self::assertEquals(['windwalker.io'], $request->getHeader('host'));
    }
}
