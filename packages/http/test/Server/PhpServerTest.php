<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Server;

use PHPUnit\Framework\TestCase;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Request\ServerRequestFactory;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Server\HttpServer;
use Windwalker\Http\Server\PhpServer;
use Windwalker\Http\Test\Mock\MockOutput;

/**
 * The PhpServerTest class.
 */
class PhpServerTest extends TestCase
{
    protected ?HttpServer $instance;

    protected MockOutput $output;

    /**
     * @see  PhpServer::listen
     */
    public function testListen(): void
    {
        $this->instance->setHandler(
            fn(PhpServer $server) => $server->handle(
                ServerRequestFactory::createFromUri(
                    'https://domain.com/hello?foo=bar'
                )
            )
        );

        $this->instance->on(
            'request',
            function (RequestEvent $event) {
                $res = $event->getRequest();

                $event->setResponse(
                    Response::fromString('Hello: ' . $res->getUri())
                );
            }
        );

        $this->instance->listen();

        self::assertEquals(
            'Hello: https://domain.com/hello?foo=bar',
            (string) $this->output->output
        );
    }

    protected function setUp(): void
    {
        $this->instance = new HttpServer(
            [],
            new PhpServer(
                $this->output = new MockOutput()
            )
        );
    }

    protected function tearDown(): void
    {
    }
}
