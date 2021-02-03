<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Test\Traits;

use Asika\SqlSplitter\SqlSplitter;
use PDOException;
use RuntimeException;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\DriverFactory;
use Windwalker\Database\Driver\Pdo\DsnHelper;
use Windwalker\Database\Event\QueryEndEvent;

/**
 * Trait DatabaseTestTrait
 */
trait DatabaseTestTrait
{
    protected static ?DatabaseAdapter $db = null;

    protected static bool $logInited = false;

    protected static array $lastQueries = [];

    protected static function createDatabase(string $driver, ?array $params = null): DatabaseAdapter
    {
        [, $platform] = DatabaseFactory::extractDriverName($driver);
        $platform = DatabaseFactory::getPlatformName($platform);

        $params = $params ?? self::getTestParams($platform);

        $params['driver']    = $driver;
        static::$lastQueries = [];

        $params = $params ?? self::getTestParams($platform);

        $db = (new DatabaseFactory())->create($driver, $params);

        // $logFile = __DIR__ . '/../tmp/all-test-sql.sql';
        //
        // if (!self::$logInited) {
        //     @unlink($logFile);
        //
        //     self::$logInited = true;
        // }

        $db->on(
            QueryEndEvent::class,
            function (QueryEndEvent $event) {
                static::$lastQueries[] = $event->getSql();

                // $fp = fopen($logFile, 'ab+');
                //
                // fwrite($fp, $event->getSql() . ";\n\n");
                //
                // fclose($fp);
            }
        );

        return static::$db = $db;
    }

    /**
     * getTestParams
     *
     * @param  string  $platform
     *
     * @return  array
     */
    protected static function getTestParams(string $platform): array
    {
        $const = 'WINDWALKER_TEST_DB_DSN_' . strtoupper($platform);

        // First let's look to see if we have a DSN defined or in the environment variables.
        if (defined($const) || getenv($const)) {
            $dsn = (defined($const) ? constant($const) : getenv($const));

            return DsnHelper::extract($dsn);
        }

        return [];
    }

    /**
     * setupDatabase
     *
     * @return  void
     */
    abstract protected static function setupDatabase(): void;

    /**
     * importFromFile
     *
     * @param  string  $file
     *
     * @return  void
     */
    protected static function importFromFile(string $file): void
    {
        if (!is_file($file)) {
            throw new RuntimeException('File not found: ' . $file);
        }

        self::importIterator(SqlSplitter::splitFromFile($file));
    }

    /**
     * importIterator
     *
     * @param  iterable  $queries
     *
     * @return  void
     */
    protected static function importIterator(iterable $queries): void
    {
        foreach ($queries as $query) {
            if (trim($query) === '') {
                continue;
            }

            try {
                static::$db->execute($query);
            } catch (PDOException $e) {
                throw new PDOException(
                    $e->getMessage() . ' - SQ: ' . $query,
                    (int) $e->getCode(),
                    $e
                );
            }
        }
    }

    /**
     * __destruct
     */
    public function __destruct()
    {
        static::$db = null;
    }

    public static function setUpBeforeClass(): void
    {
        static::setupDatabase();
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        if (static::$db) {
            static::$db->disconnect();
            static::$db = null;
        }
    }
}
