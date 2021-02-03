<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test;

use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Event\QueryEndEvent;

use function Windwalker\disposable;

/**
 * The AbstractDatabaseTestCase class.
 */
abstract class AbstractDatabaseTestCase extends AbstractDatabaseDriverTestCase
{
    protected static string $platform = 'MySQL';

    protected static string $driver = 'pdo_mysql';

    protected static bool $logInited = false;

    protected static ?DatabaseAdapter $db;

    protected static array $lastQueries = [];

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$db = self::createAdapter();
    }

    protected static function createAdapter(?array $params = null): DatabaseAdapter
    {
        $params           = $params ?? self::getTestParams();
        $params['driver'] = static::$driver;
        static::$lastQueries = [];

        $db = (new DatabaseFactory())->createByDriverName(
            static::$driver,
            $params
        );

        $logFile = __DIR__ . '/../tmp/test-sql.sql';

        if (!self::$logInited) {
            @unlink($logFile);

            self::$logInited = true;
        }

        $db->on(QueryEndEvent::class, function (QueryEndEvent $event) use ($logFile) {
            static::$lastQueries[] = $event->getSql();

            $fp = fopen($logFile, 'ab+');

            fwrite($fp, $event->getSql() . ";\n\n");

            fclose($fp);
        });

        return $db;
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        static::$db->getDriver()->disconnectAll();
        static::$db = null;
    }

    public function logQueries(callable $callback, &$result = null): array
    {
        $logs = [];
        $fp = function (QueryEndEvent $event) use (&$logs) {
            return $logs[] = $event->getSql();
        };

        static::$db->on(QueryEndEvent::class, $fp);

        $result = $callback();

        static::$db->getDispatcher()->remove($fp);

        return $logs;
    }
}
