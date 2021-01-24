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
use ReflectionException;
use Windwalker\Http\Test\Stub\StubContentTypeResponse;
use Windwalker\Test\TestHelper;

/**
 * Test class of AbstractContentTypeResponse
 *
 * @since 3.0
 */
class AbstractContentTypeResponseTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var StubContentTypeResponse
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
        $this->instance = new StubContentTypeResponse('Flower');
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
     *
     * @covers \Windwalker\Http\Response\AbstractContentTypeResponse::__construct
     */
    public function testConstruct()
    {
        $response = new StubContentTypeResponse('Flower', 123, ['x-foo' => 'bar']);

        $this->assertEquals(['text/plain; charset=utf-8'], $response->getHeader('content-type'));
        $this->assertEquals(['bar'], $response->getHeader('x-foo'));
        $this->assertEquals('Flower', $response->getBody()->__toString());
        $this->assertEquals(123, $response->getStatusCode());
    }

    /**
     * Method to test withContentType().
     *
     * @return void
     *
     * @covers \Windwalker\Http\Response\AbstractContentTypeResponse::withContentType
     */
    public function testWithContentType()
    {
        $response = $this->instance->withContentType('application/flower');

        $this->assertEquals(['application/flower; charset=utf-8'], $response->getHeader('content-type'));
    }

    /**
     * testNormalizeContentType
     *
     * @return  void
     *
     * @throws ReflectionException
     * @covers \Windwalker\Http\Response\AbstractContentTypeResponse::normalizeContentType
     */
    public function testNormalizeContentType()
    {
        $this->assertEquals('text/plain', TestHelper::invoke($this->instance, 'normalizeContentType', 'Text/Plain'));
    }
}
