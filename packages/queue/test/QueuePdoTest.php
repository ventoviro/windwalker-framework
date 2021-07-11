<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Test;

use Windwalker\Queue\Driver\PdoQueueDriver;
use Windwalker\Queue\Queue;

/**
 * The QueueAdapterTest class.
 */
class QueuePdoTest extends QueueDatabaseTest
{
    protected function setUp(): void
    {
        $conn = static::$db->getDriver()->getConnection();

        $this->instance = new Queue(new PdoQueueDriver($conn->get()));

        $conn->release();
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
