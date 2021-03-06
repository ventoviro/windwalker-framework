<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Test\Driver\AbstractDriverTest;

/**
 * The PdoSqliteDriverTest class.
 */
class PdoSqliteDriverTest extends AbstractDriverTest
{
    protected static string $platform = AbstractPlatform::SQLITE;

    protected static string $driverName = 'pdo_sqlite';
}
