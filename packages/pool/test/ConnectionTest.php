<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool\Test;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Windwalker\Pool\AbstractConnection;
use Windwalker\Pool\AbstractPool;
use Windwalker\Pool\Stack\PhpStack;
use Windwalker\Pool\Test\Stub\StubConnection;
use Windwalker\Pool\Test\Stub\StubConnectionPool;

/**
 * The ConnectionTest class.
 */
class ConnectionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected ?AbstractConnection $instance;

    /**
     * @see  AbstractConnection::setActive
     */
    public function testSetActive(): void
    {
        $this->instance->setActive(true);

        self::assertTrue($this->instance->isActive());

        $this->instance->setActive(false);

        self::assertFalse($this->instance->isActive());
    }

    /**
     * @see  AbstractConnection::getLastTime
     */
    public function testGetLastTime(): void
    {
        self::assertEquals(0, $this->instance->getLastTime());

        $this->instance->updateLastTime();

        self::assertNotEquals(0, $this->instance->getLastTime());
    }

    /**
     * @see  AbstractConnection::getId
     */
    public function testGetId(): void
    {
        self::assertEquals(0, $this->instance->getId());

        $this->instance->setPool(static::createPool());

        self::assertEquals(1, $this->instance->getId());
    }

    /**
     * @see  AbstractConnection::release
     */
    public function testRelease(): void
    {
        $pool = Mockery::mock(AbstractPool::class);
        $pool->shouldReceive('release');

        $pool->shouldReceive('getSerial')
            ->andReturn(123);

        $this->instance->setPool($pool);

        $this->instance->release(true);
    }

    /**
     * @see  AbstractConnection::reconnect
     */
    public function testReconnect(): void
    {
        $this->instance->reconnect();

        self::assertEquals('Hello', $this->instance->reconnect());
    }

    protected function setUp(): void
    {
        $this->instance = new StubConnection();
    }

    protected function tearDown(): void
    {
    }

    protected static function createPool(): StubConnectionPool
    {
        return new StubConnectionPool(
            [],
            new PhpStack()
        );
    }
}
