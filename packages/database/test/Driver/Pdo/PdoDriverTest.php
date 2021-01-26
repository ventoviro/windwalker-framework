<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use Windwalker\Database\Driver\Pdo\PdoSqlsrvConnection;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Test\Driver\AbstractDriverTest;

/**
 * The PdoDriverTest class.
 */
class PdoDriverTest extends AbstractDriverTest
{
    protected static string $platform = AbstractPlatform::MYSQL;

    protected static string $driverName = 'pdo_mysql';

    /**
    //  * @see  AbstractDriver::setPlatformName
    //  */
    // public function testSetPlatformName(): void
    // {
    //     $driver = self::createDriver([]);
    //     $driver->setPlatformName('sqlsrv');
    //     $conn = $driver->createConnection();
    //
    //     self::assertInstanceOf(
    //         PdoSqlsrvConnection::class,
    //         $conn
    //     );
    // }

    /**
     * @see  AbstractDriver::quote
     */
    public function testQuote(): void
    {
        self::assertEquals(
            "'foo\'s #hello --options'",
            static::$driver->quote("foo's #hello --options")
        );
    }

    /**
     * @see  AbstractDriver::escape
     */
    public function testEscape(): void
    {
        self::assertEquals(
            "foo\'s #hello --options",
            static::$driver->escape("foo's #hello --options")
        );
    }
}
