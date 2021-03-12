<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test;

use Asika\SqlSplitter\SqlSplitter;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\Pdo\AbstractPdoConnection;
use Windwalker\Database\Driver\Pdo\DsnHelper;
use Windwalker\Database\Test\Reseter\AbstractReseter;
use Windwalker\Query\Escaper;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Query;
use Windwalker\Test\Traits\QueryTestTrait;

/**
 * The AbstractDatabaseTestCase class.
 */
abstract class AbstractDatabaseDriverTestCase extends TestCase
{
    use QueryTestTrait;

    protected static string $platform = '';

    protected static string $dbname = '';

    /**
     * @var \PDO
     */
    protected static ?\PDO $baseConn;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $params = static::getTestParams();

        if (!$params) {
            self::markTestSkipped('DSN of ' . static::$platform . ' not available for test case: ' . static::class);
        }

        $platform = static::$platform;

        $platform = DatabaseFactory::getDriverShortName($platform);

        /** @var AbstractPdoConnection|string $connClass */
        $connClass = 'Windwalker\Database\Driver\Pdo\Pdo' . ucfirst($platform) . 'Connection';

        if (!class_exists($connClass) || !is_subclass_of($connClass, AbstractPdoConnection::class)) {
            throw new \LogicException(
                sprintf(
                    '%s should exists and extends %s',
                    $connClass,
                    AbstractPdoConnection::class
                )
            );
        }

        $reseter = AbstractReseter::create(static::$platform);

        static::$dbname = $params['dbname'];
        unset($params['dbname']);

        $pdo = static::createBaseConnect($params, $connClass);

        $reseter->createDatabase($pdo, static::$dbname);

        // Disconnect.
        $pdo = null;

        $params['dbname'] = static::$dbname;

        static::$baseConn = static::createBaseConnect($params, $connClass);

        $reseter->clearAllTables(static::$baseConn, static::$dbname);

        static::setupDatabase();
    }

    protected static function createBaseConnect(array $params, string $connClass): \PDO
    {
        $dsn = $connClass::getParameters($params)['dsn'];

        return new \PDO(
            $dsn,
            $params['user'] ?? null,
            $params['password'] ?? null,
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]
        );
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
            throw new \RuntimeException('File not found: ' . $file);
        }

        self::importIterator(
            SqlSplitter::splitSqlString(
                file_get_contents($file)
            )
        );
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
                static::$baseConn->exec($query);
            } catch (\PDOException $e) {
                throw new \PDOException(
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
        // static::$baseConn->exec('DROP DATABASE ' . static::qn(static::$dbname));

        static::$baseConn = null;
    }

    /**
     * getTestParams
     *
     * @return  array
     */
    protected static function getTestParams(): array
    {
        $const = 'WINDWALKER_TEST_DB_DSN_' . strtoupper(static::$platform);

        // First let's look to see if we have a DSN defined or in the environment variables.
        if (defined($const) || getenv($const)) {
            $dsn = (defined($const) ? constant($const) : getenv($const));

            return DsnHelper::extract($dsn);
        }

        return [];
    }

    /**
     * getGrammar
     *
     * @param mixed $escaper
     *
     * @return  AbstractGrammar
     */
    public static function getGrammar(mixed $escaper = null): AbstractGrammar
    {
        $grammar = AbstractGrammar::create(static::$platform);

        if ($escaper) {
            $grammar->setEscaper(new Escaper($escaper));
        }

        return $grammar;
    }

    public static function createQuery($escaper = null): Query
    {
        return new Query($escaper ?: static::$baseConn, static::getGrammar());
    }

    /**
     * quote
     *
     * @param  string  $text
     *
     * @return  string
     */
    protected static function qn(string $text): string
    {
        return static::getGrammar()::quoteName($text);
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        static::$baseConn = null;
    }
}
