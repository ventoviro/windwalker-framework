<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Platform;

use Windwalker\Database\Platform\MySQLPlatform;
use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\Database\Test\Reseter\AbstractReseter;

/**
 * The AbstractPlatformTest class.
 */
abstract class AbstractPlatformTest extends AbstractDatabaseTestCase
{
    /**
     * @var MySQLPlatform
     */
    protected $instance;

    protected function setUp(): void
    {
        $this->instance = static::$db->getPlatform();
    }

    /**
     * Method to test start().
     *
     * @return void
     */
    // public function testTransactionRollback()
    // {
    //     $table = '#__flower';
    //
    //     $sql = "INSERT INTO {$table} (title, meaning, params) VALUES ('A', '', ''), ('B', '', ''), ('C', '', '')";
    //
    //     $this->instance->transactionStart();
    //
    //     static::$db->execute($sql);
    //
    //     $this->instance->transactionRollback();
    //
    //     $result = static::$db->prepare('SELECT title FROM #__flower WHERE title = \'A\'')->loadResult();
    //
    //     $this->assertNull($result);
    // }

    /**
     * Method to test start().
     *
     * @return void
     */
    public function testTransactionCommit()
    {
        $reseter = AbstractReseter::create(static::$platform);
        $reseter->clearAllTables(static::$baseConn, static::$dbname);

        self::importFromFile(__DIR__ . '/../stub/' . static::$platform . '.sql');

        $table = '#__flower';

        $sql = "INSERT INTO {$table} (title, meaning, params) VALUES ('A', '', ''), ('B', '', ''), ('C', '', '')";

        $tran = $this->instance->transactionStart();

        static::$db->execute($sql);

        $this->instance->transactionCommit();

        $result = static::$db->prepare('SELECT title FROM #__flower WHERE title = \'A\'')->result();

        $this->assertEquals('A', $result);
    }

    /**
     * testTransactionNested
     *
     * @return  void
     */
    public function testTransactionNested()
    {
        $table = '#__flower';

        // Level 1
        $sql = "INSERT INTO {$table} (title, meaning, params) VALUES ('D', '', '')";

        $tran = $this->instance->transactionStart();

        static::$db->execute($sql);

        // Level 2
        $sql = "INSERT INTO {$table} (title, meaning, params) VALUES ('E', '', '')";

        $tran = $this->instance->transactionStart();

        static::$db->execute($sql);

        $this->instance->transactionRollback();
        $this->instance->transactionCommit();

        $result = static::$db->prepare('SELECT title FROM #__flower WHERE title = \'D\'')->result();
        $this->assertEquals('D', $result);

        $result2 = static::$db->prepare('SELECT title FROM #__flower WHERE title = \'E\'')->result();
        $this->assertNotEquals('E', $result2);
    }

    public function testGetCurrentDatabase(): void
    {
        self::assertEquals(
            self::$db->getDriver()->getOption('database'),
            $this->instance->getCurrentDatabase()
        );
    }

    public function testCreateDropDatabase(): void
    {
        if (in_array('hello', $this->instance->listDatabases(), true)) {
            $this->instance->dropDatabase('hello');
        }

        $this->instance->createDatabase('hello');

        self::assertContains(
            'hello',
            $this->instance->listDatabases()
        );

        $this->instance->dropDatabase('hello');
    }

    public function testCreateDropSchema(): void
    {
        if (in_array('hello', $this->instance->listSchemas(), true)) {
            $this->instance->dropSchema('hello', []);
        }

        $this->instance->createSchema('hello');

        self::assertContains(
            'hello',
            $this->instance->listSchemas()
        );

        $this->instance->dropSchema('hello', []);
    }

    protected function tearDown(): void
    {
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/../stub/metadata/' . static::$platform . '.sql');
    }
}
