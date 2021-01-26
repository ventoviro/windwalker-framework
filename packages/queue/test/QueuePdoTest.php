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
use Windwalker\Queue\Driver\PdoQueueDriver;
use Windwalker\Queue\Job\JobInterface;
use Windwalker\Queue\Queue;
use Windwalker\Queue\Test\Stub\TestJob;

/**
 * The QueueAdapterTest class.
 */
class QueuePdoTest extends QueueDatabaseTest
{
    protected function setUp(): void
    {
        $this->instance = new Queue(
            new PdoQueueDriver(static::$db->getDriver()->getConnection()->get())
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
