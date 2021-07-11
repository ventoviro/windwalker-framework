<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\Queue\Driver\DatabaseQueueDriver;
use Windwalker\Queue\Event\LoopEndEvent;
use Windwalker\Queue\Queue;
use Windwalker\Queue\Worker;

use Windwalker\Test\Traits\DatabaseTestTrait;
use Windwalker\Utilities\Cache\RuntimeCacheTrait;

use function Windwalker\closure;

/**
 * The WorkerTest class.
 */
class WorkerTest extends TestCase
{
    use DatabaseTestTrait;

    protected ?Worker $instance = null;

    public static array $logs = [];

    /**
     * @see  Worker::loop
     */
    public function testLoop(): void
    {
        // if (!in_array('closure', stream_get_wrappers(), true)) {
        //     self::markTestSkipped('Closure serialize not supported now.');
        // }

        $this->instance->getQueue()->push(
            static function () {
                static::$logs[] = 'Job executed.';
            },
            0,
            'hello'
        );

        $this->instance->on(LoopEndEvent::class, fn () => $this->instance->stop());

        $this->instance->loop(['default', 'hello'], ['sleep' => 0.1]);

        self::assertEquals(
            'Job executed.',
            static::$logs[0]
        );

        self::$logs = [];
    }

    /**
     * @see  Worker::stopIfNecessary
     */
    public function testStopIfNecessary(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Worker::getState
     */
    public function testGetState(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Worker::stop
     */
    public function testStop(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Worker::runNextJob
     */
    public function testRunNextJob(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Worker::setState
     */
    public function testSetState(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Worker::process
     */
    public function testProcess(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Worker::__construct
     */
    public function test__construct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Worker::shutdown
     */
    public function testShutdown(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Worker::getQueue
     */
    public function testGetAdapter(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->instance = new Worker(
            new Queue(
                new DatabaseQueueDriver(self::$db)
            )
        );
    }

    protected function tearDown(): void
    {
    }

    /**
     * setupDatabase
     *
     * @return  void
     */
    protected static function setupDatabase(): void
    {
        self::createDatabase('pdo_mysql');
        self::importFromFile(__DIR__ . '/../resources/sql/queue_jobs.sql');
    }
}
