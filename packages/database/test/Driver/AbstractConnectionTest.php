<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver;

use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Test\AbstractDatabaseDriverTestCase;
use Windwalker\Utilities\Arr;

/**
 * The AbstractConnectionTest class.
 */
abstract class AbstractConnectionTest extends AbstractDatabaseDriverTestCase
{
    protected static string $platform = '';

    /**
     * @var AbstractConnection
     */
    protected static string $className = AbstractConnection::class;

    /**
     * @var AbstractConnection
     */
    protected $instance;

    protected static function setupDatabase(): void
    {
    }

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        $className = static::$className;

        if (!$className::isSupported()) {
            self::markTestSkipped('Driver for: ' . $className . ' not available.');
        }

        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        $this->instance = static::createConnection();
    }

    protected static function createConnection(): AbstractConnection
    {
        $className = static::$className;

        return new $className(
            Arr::only(
                self::getTestParams(),
                [
                    'host',
                    'user',
                    'password',
                    'database',
                    'port'
                ]
            )
        );
    }

    /**
     * assertConnected
     *
     * @param  AbstractConnection  $conn
     *
     * @return  void
     */
    abstract public function assertConnected(AbstractConnection $conn): void;

    public function testConnect(): void
    {
        $conn = $this->instance;
        $conn->connect();

        $this->assertConnected($conn);
    }

    public function testConnectWrong()
    {
        $conn = $this->instance;
        $conn->setOption('user', 'notexists');
        $conn->setOption('password', 'This-is-wrong-password');

        $this->expectException(\RuntimeException::class);
        $conn->connect();
    }

    public function testDisconnect(): void
    {
        $conn = $this->instance;
        $conn->connect();

        $conn->disconnect();

        self::assertNull($conn->get());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->instance->disconnect();
        $this->instance = null;
    }
}
