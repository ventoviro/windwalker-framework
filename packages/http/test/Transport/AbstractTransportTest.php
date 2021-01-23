<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Transport;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Exception\HttpRequestException;
use Windwalker\Http\Helper\UriHelper;
use Windwalker\Http\Request\Request;
use Windwalker\Http\Transport\AbstractTransport;
use Windwalker\Http\Uri;
use Windwalker\Stream\Stream;
use Windwalker\Stream\StringStream;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * Test class of CurlTransport
 *
 * @since 2.1
 */
abstract class AbstractTransportTest extends TestCase
{
    use BaseAssertionTrait;

    /**
     * Property options.
     *
     * @var  array
     */
    protected array $options = [
        'options' => [],
    ];

    /**
     * Test instance.
     *
     * @var AbstractTransport
     */
    protected $instance;

    /**
     * setUpBeforeClass
     *
     * @return  void
     */
    public static function setUpBeforeClass(): void
    {
        if (!defined('WINDWALKER_TEST_HTTP_URL')) {
            static::markTestSkipped('No WINDWALKER_TEST_HTTP_URL provided');
        }
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        if (!$this->instance::isSupported()) {
            $this->markTestSkipped(get_class($this->instance) . ' driver not supported.');
        }
    }

    /**
     * createRequest
     *
     * @param StreamInterface $stream
     *
     * @return Request
     */
    protected function createRequest($stream = null): Request
    {
        return new Request($stream ?: new StringStream());
    }

    /**
     * testRequestGet
     *
     * @return  void
     */
    public function testRequestGet()
    {
        $request = $this->createRequest();

        $request = $request->withUri(new Uri(WINDWALKER_TEST_HTTP_URL . '/json?foo=bar'))
            ->withMethod('GET');

        $response = $this->instance->request($request);

        self::assertEquals(200, $response->getStatusCode());
        self::assertJson($response->getBody()->getContents());

        $request = $this->createRequest();

        $request = $request->withUri(new Uri(WINDWALKER_TEST_HTTP_URL . '/json?foo=bar&baz[3]=yoo'))
            ->withMethod('GET');

        $response = $this->instance->request($request);

        $data = json_decode($response->getBody()->getContents(), true);
        self::assertEquals(['foo' => 'bar', 'baz' => [3 => 'yoo']], $data);
    }

    /**
     * testBadDomainGet
     *
     * @return  void
     */
    public function testBadDomainGet()
    {
        $this->expectException(\RuntimeException::class);

        $request = $this->createRequest();

        $request = $request->withUri(new Uri('http://not.exists.url/flower.sakura'))
            ->withMethod('GET');

        $this->instance->request($request);
    }

    /**
     * testBadPathGet
     *
     * @return  void
     */
    public function testBadPathGet()
    {
        $request = $this->createRequest();

        $request = $request->withUri(new Uri(WINDWALKER_TEST_HTTP_URL . '/wrong'))
            ->withMethod('POST');

        $request->getBody()->write(UriHelper::buildQuery(['foo' => 'bar']));

        $response = $this->instance->request($request);

        self::assertEquals(404, $response->getStatusCode());
        self::assertEquals('Not Found', $response->getReasonPhrase());
    }

    /**
     * testRequestPost
     *
     * @return  void
     */
    public function testRequestPost()
    {
        $request = $this->createRequest();

        $request = $request->withUri(new Uri(WINDWALKER_TEST_HTTP_URL))
            ->withMethod('POST');

        $request->getBody()->write(UriHelper::buildQuery(['foo' => 'bar']));

        $response = $this->instance->request($request);

        self::assertStringSafeEquals(
            'foo=bar',
            $response->getBody()->getContents()
        );
    }

    /**
     * testRequestPut
     *
     * @return  void
     */
    public function testRequestPut()
    {
        $request = $this->createRequest();

        $request = $request->withUri(new Uri(WINDWALKER_TEST_HTTP_URL))
            ->withMethod('PUT');

        $request->getBody()->write(UriHelper::buildQuery(['foo' => 'bar']));

        $response = $this->instance->request($request);

        self::assertStringSafeEquals(
            'foo=bar',
            $response->getBody()->getContents()
        );
    }

    /**
     * testRequestCredentials
     *
     * @return  void
     */
    public function testRequestCredentials()
    {
        $request = $this->createRequest();

        $uri = new Uri(WINDWALKER_TEST_HTTP_URL . '/auth');
        $uri = $uri->withUserInfo('username', 'pass1234');

        $request = $request->withUri($uri)
            ->withMethod('GET');

        $response = $this->instance->request($request);

        self::assertStringSafeEquals(
            'username:pass1234',
            $response->getBody()->getContents()
        );
    }

    /**
     * testRequestPostScalar
     *
     * @return  void
     */
    public function testRequestPostScalar()
    {
        $request = $this->createRequest();

        $request = $request->withUri(new Uri(WINDWALKER_TEST_HTTP_URL . '?foo=bar'))
            ->withMethod('POST');

        $request->getBody()->write('flower=sakura');

        $response = $this->instance->request($request);

        self::assertStringSafeEquals(
            'flower=sakura',
            $response->getBody()->getContents()
        );
    }

    /**
     * testDownload
     *
     * @return  void
     */
    public function testDownload()
    {
        $root = vfsStream::setup(
            'root',
            0755,
            [
                'download' => []
            ]
        );

        $dest = 'vfs://root/download/downloaded.tmp';

        self::assertFileDoesNotExist($dest);

        $request = $this->createRequest(new Stream());

        $src = WINDWALKER_TEST_HTTP_URL;

        $request = $request->withUri(new Uri($src . '/json?foo=bar'))
            ->withMethod('GET');

        $response = $this->instance->download($request, $dest);

        self::assertStringSafeEquals(
            '{"foo":"bar"}',
            trim(file_get_contents($dest))
        );
    }

    protected function getTestUrl(): Uri
    {
        return new Uri(WINDWALKER_TEST_HTTP_URL);
    }

    protected function getHost(): string
    {
        $uri = $this->getTestUrl();

        return $uri->toString(Uri::HOST | Uri::PORT);
    }

    /**
     * @inheritDoc
     */
    protected function runTest()
    {
        try {
            return parent::runTest();
        } catch (HttpRequestException $e) {
            if (str_contains($e->getMessage(), 'Connection refused')) {
                throw new HttpRequestException(
                    $e->getMessage() . ' - Try run: ' . sprintf(
                        'php -S %s:%s bin/test-server.php',
                        $this->getTestUrl()->getHost(),
                        $this->getTestUrl()->getPort()
                    )
                );
            }

            throw $e;
        }
    }
}
