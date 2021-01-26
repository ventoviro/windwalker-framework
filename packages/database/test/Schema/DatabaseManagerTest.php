<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Schema;

use Windwalker\Database\Schema\DatabaseManager;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

/**
 * The DatabaseManagerTest class.
 */
class DatabaseManagerTest extends AbstractDatabaseTestCase
{
    protected static string $platform = 'MySQL';

    protected static string $driver = 'pdo_mysql';

    /**
     * @var DatabaseManager
     */
    protected $instance;

    /**
     * @see  DatabaseManager::create
     */
    public function testCreate(): void
    {
        $dbname = static::$db->getOption('database');

        $newDbname = $dbname . '_new';

        $dbManager = static::$db->getDatabase($newDbname);

        self::assertFalse($dbManager->exists());

        $dbManager->create();

        self::assertTrue($dbManager->exists());
    }

    /**
     * @see  DatabaseManager::drop
     */
    public function testDrop(): void
    {
        $dbname = static::$db->getOption('database');

        $newDbname = $dbname . '_new';

        $dbManager = static::$db->getDatabase($newDbname);

        $dbManager->drop();

        $dbs = static::$db->listDatabases();

        self::assertNotContains(
            $newDbname,
            $dbs
        );
    }

    protected function setUp(): void
    {
        $this->instance = null;
    }

    protected function tearDown(): void
    {
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
    }
}
