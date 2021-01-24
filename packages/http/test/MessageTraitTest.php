<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Test\Stub\StubMessage;
use Windwalker\Stream\Stream;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * Test class of AbstractMessage
 *
 * @since 2.1
 */
class MessageTraitTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?StubMessage $message;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->message = new StubMessage();
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
     * Method to test getProtocolVersion().
     *
     * @return void
     */
    public function testWithAndSetProtocolVersion()
    {
        self::assertEquals('1.1', $this->message->getProtocolVersion());

        $message = $this->message->withProtocolVersion('1.0');

        self::assertNotSame($this->message, $message);
        self::assertEquals('1.0', $message->getProtocolVersion());

        // Wrong type
        self::assertExpectedException(
            function () use ($message) {
                $message->withProtocolVersion(1.0);
            },
            InvalidArgumentException::class
        );
    }

    /**
     * Method to test getHeader().
     *
     * @return void
     *


     */
    public function testWithAndGetHeader()
    {
        $message = $this->message->withHeader('Content-Type', 'text/json');

        self::assertNotSame($this->message, $message);
        self::assertEquals(['text/json'], $message->getHeader('Content-Type'));
        self::assertEquals(['text/json'], $message->getHeader('content-type'));

        $message = $this->message->withHeader('X-Foo', ['Foo', 'Bar']);

        self::assertNotSame($this->message, $message);
        self::assertEquals(['Foo', 'Bar'], $message->getHeader('X-Foo'));
    }

    /**
     * Method to test hasHeader().
     *
     * @return void
     *

     */
    public function testHasHeader()
    {
        self::assertFalse($this->message->hasHeader('X-Foo'));

        $message = $this->message->withHeader('Content-Type', 'text/json');

        self::assertTrue($message->hasHeader('Content-Type'));
        self::assertTrue($message->hasHeader('content-type'));
    }

    /**
     * Method to test getHeaders().
     *
     * @return void
     *

     */
    public function testGetHeaders()
    {
        self::assertEquals([], $this->message->getHeaders());

        $message = $this->message->withHeader('X-Foo', ['Foo', 'Bar']);
        $message = $message->withHeader('X-Bar', ['Flower', 'Sakura']);

        $expected = [
            'X-Foo' => ['Foo', 'Bar'],
            'X-Bar' => ['Flower', 'Sakura'],
        ];

        self::assertEquals($expected, $message->getHeaders());
    }

    /**
     * Method to test getHeaderLine().
     *
     * @return void
     *

     */
    public function testGetHeaderLine()
    {
        self::assertEquals('', $this->message->getHeaderLine('X-Foo'));

        $message = $this->message->withHeader('X-Foo', ['Foo', 'Bar']);

        self::assertEquals('Foo,Bar', $message->getHeaderLine('X-Foo'));
        self::assertEquals('Foo,Bar', $message->getHeaderLine('x-foo'));
        self::assertSame('', $message->getHeaderLine('x-bar'));
    }

    /**
     * Method to test withAddedHeader().
     *
     * @return void
     *

     */
    public function testWithAddedHeader()
    {
        $message = $this->message->withAddedHeader('X-Foo', 'One');

        self::assertNotSame($this->message, $message);
        self::assertEquals(['One'], $message->getHeader('X-Foo'));

        $message = $message->withAddedHeader('X-Foo', 'Two');

        self::assertEquals(['One', 'Two'], $message->getHeader('X-Foo'));

        $message = $message->withAddedHeader('X-Foo', ['Three', 'Four']);

        self::assertEquals(['One', 'Two', 'Three', 'Four'], $message->getHeader('X-Foo'));
    }

    /**
     * Method to test withoutHeader().
     *
     * @return void
     *

     */
    public function testWithoutHeader()
    {
        $message = $this->message->withAddedHeader('X-Foo', 'One');

        self::assertNotSame($this->message, $message);
        self::assertEquals(['One'], $message->getHeader('X-Foo'));

        $message2 = $message->withoutHeader('X-Foo');

        self::assertNotSame($this->message, $message2);
        self::assertEquals([], $message2->getHeader('X-Foo'));

        $message3 = $message->withoutHeader('x-foo');

        self::assertNotSame($this->message, $message3);
        self::assertEquals([], $message3->getHeader('X-Foo'));
    }

    /**
     * Method to test getBody().
     *
     * @return void
     *

     */
    public function testWithAndGetBody()
    {
        $message = $this->message->withBody(new Stream());

        self::assertNotSame($this->message, $message);
        self::assertInstanceOf(StreamInterface::class, $message->getBody());
    }
}
