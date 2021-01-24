<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use PHPUnit\Framework\TestCase;
use Windwalker\Http\Response\Response;
use Windwalker\Stream\Stream;

/**
 * Test class of Response
 *
 * @since 2.1
 */
class ResponseTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var Response
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
        $this->instance = new Response();
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

    public function testConstruct()
    {
        // Test no params
        $res = new Response();

        self::assertInstanceOf(Stream::class, $res->getBody());
        self::assertEquals('php://memory', $res->getBody()->getMetadata('uri'));
        self::assertEquals(200, $res->getStatusCode());
        self::assertEquals([], $res->getHeaders());

        // Test with params
        $body    = fopen($tmpfile = tempnam(sys_get_temp_dir(), 'windwalker'), 'wb+');
        $headers = [
            'X-Foo' => ['Flower', 'Sakura'],
            'Content-Type' => 'application/json',
        ];

        $res = new Response($body, 404, $headers);

        self::assertInstanceOf(Stream::class, $res->getBody());
        self::assertEquals($tmpfile, $res->getBody()->getMetadata('uri'));
        self::assertEquals(['Flower', 'Sakura'], $res->getHeader('x-foo'));
        self::assertEquals(['application/json'], $res->getHeader('content-type'));

        fclose($body);

        // Test with object params
        $body = new Stream();
        $res  = new Response($body);

        self::assertSame($body, $res->getBody());
    }

    /**
     * Method to test getStatusCode().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Response\Response::getStatusCode()
     * @covers \Windwalker\Http\Response\Response::withStatus
     */
    public function testWithAndGetStatusCode()
    {
        self::assertEquals(200, $this->instance->getStatusCode());

        $res = $this->instance->withStatus(403);

        self::assertNotSame($res, $this->instance);
        self::assertEquals(403, $res->getStatusCode());

        $res = $res->withStatus(500, 'Unknown error');

        self::assertEquals(500, $res->getStatusCode());
        self::assertEquals('Unknown error', $res->getReasonPhrase());
    }

    /**
     * Method to test getReasonPhrase().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Response\Response::getReasonPhrase
     */
    public function testGetReasonPhrase()
    {
        $res = new Response();

        $res = $res->withStatus(200);

        self::assertEquals('OK', $res->getReasonPhrase());

        $res = $res->withStatus(400);

        self::assertEquals('Bad Request', $res->getReasonPhrase());

        $res = $res->withStatus(404);

        self::assertEquals('Not Found', $res->getReasonPhrase());

        $res = $res->withStatus(500);

        self::assertEquals('Internal Server Error', $res->getReasonPhrase());
    }
}
