<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Mysqli;

use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Test\Driver\AbstractDriverTest;

/**
 * The MysqliDriverTest class.
 */
class MysqliDriverTest extends AbstractDriverTest
{
    protected static string $platform = AbstractPlatform::MYSQL;

    protected static string $driverName = 'mysqli';

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
