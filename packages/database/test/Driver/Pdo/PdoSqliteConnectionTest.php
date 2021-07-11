<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use RuntimeException;
use Windwalker\Database\Driver\Pdo\PdoSqliteConnection;

/**
 * The PdoSqliteConnection class.
 */
class PdoSqliteConnectionTest extends AbstractPdoConnectionTest
{
    protected static string $platform = 'sqlite';

    protected static string $className = PdoSqliteConnection::class;

    public function testConnectWrong()
    {
        $conn = $this->instance;

        // Direct to self so that sqlite unable to create db
        $conn->setOption('database', __DIR__);

        $this->expectException(RuntimeException::class);
        $conn->connect();
    }
}
