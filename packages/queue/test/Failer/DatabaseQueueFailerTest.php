<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Test\Failer;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Queue\Failer\DatabaseQueueFailer;
use Windwalker\Queue\Failer\QueueFailerInterface;
use Windwalker\Test\Traits\DatabaseTestTrait;

/**
 * The DatabaseQueueFailerTest class.
 */
class DatabaseQueueFailerTest extends TestCase
{
    use DatabaseTestTrait;

    /**
     * @var QueueFailerInterface|null
     */
    protected ?QueueFailerInterface $instance = null;

    /**
     * @see  DatabaseQueueFailer::add
     */
    public function testAdd(): void
    {
        $this->instance->add(
            'test',
            'default',
            '{}',
            RuntimeException::class
        );
        $r = $this->instance->add(
            'hello',
            'world',
            '{}',
            RuntimeException::class
        );

        self::assertEquals(
            2,
            $r
        );

        $item = self::$db->select('*')
            ->from('queue_failed_jobs')
            ->where('id', 1)
            ->get();

        self::assertEquals(
            'test',
            $item->connection
        );
        self::assertEquals(
            '{}',
            $item->body
        );
        self::assertEquals(
            RuntimeException::class,
            $item->exception
        );
    }

    /**
     * @see  DatabaseQueueFailer::__construct
     */
    public function testConstruct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  DatabaseQueueFailer::setTable
     */
    public function testSetTable(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  DatabaseQueueFailer::get
     */
    public function testGet(): void
    {
        $item = $this->instance->get(2);

        self::assertEquals(
            2,
            $item['id']
        );

        self::assertEquals(
            'hello',
            $item['connection']
        );

        self::assertEquals(
            'world',
            $item['channel']
        );
    }

    /**
     * @see  DatabaseQueueFailer::all
     */
    public function testAll(): void
    {
        $items = $this->instance->all();

        self::assertEquals(
            ['test', 'hello'],
            array_column($items, 'connection')
        );
    }

    /**
     * @see  DatabaseQueueFailer::remove
     */
    public function testRemove(): void
    {
        $this->instance->remove(1);

        self::assertNull(
            self::$db->select('*')
                ->from('queue_failed_jobs')
                ->where('id', 1)
                ->get()
        );
    }

    /**
     * @see  DatabaseQueueFailer::clear
     */
    public function testClear(): void
    {
        $this->instance->clear();

        self::assertEquals(
            [],
            self::$db->select('*')
                ->from('queue_failed_jobs')
                ->all()
                ->dump()
        );
    }

    /**
     * @see  DatabaseQueueFailer::getTable
     */
    public function testGetTable(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  DatabaseQueueFailer::isSupported
     */
    public function testIsSupported(): void
    {
        self::assertTrue($this->instance->isSupported());
    }

    protected function setUp(): void
    {
        $this->instance = new DatabaseQueueFailer(
            self::$db
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

        self::importFromFile(__DIR__ . '/../../resources/sql/queue_failed_jobs.sql');
    }
}
