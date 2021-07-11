<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool\Test\Stack;

use PHPUnit\Framework\TestCase;
use Swoole\Timer;
use Windwalker\Pool\Stack\SwooleStack;
use Windwalker\Pool\Test\Stub\StubConnection;
use Windwalker\Test\Traits\Reactor\SwooleTestTrait;

use function Swoole\Coroutine\run;

/**
 * The SwooleStackTest class.
 */
class SwooleStackTest extends TestCase
{
    use SwooleTestTrait;

    protected ?SwooleStack $instance;

    public function testSwooleWait(): void
    {
        $v = null;

        run(
            function () use (&$v) {
                $this->instance->push($stub = new StubConnection());

                $conn = $this->instance->pop();

                Timer::after(
                    300,
                    function () use ($conn) {
                        $this->instance->push($conn);
                    }
                );

                $conn = $this->instance->pop();
                $v = $conn;

                self::assertSame($conn, $stub);
            }
        );

        self::assertNotNull($v);
    }

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        self::skipIfSwooleNotInstalled();
    }

    protected function setUp(): void
    {
        $this->instance = new SwooleStack(10);
    }

    protected function tearDown(): void
    {
    }
}
