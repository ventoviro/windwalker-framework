<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use PDO;
use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Driver\Pdo\PdoMysqlConnection;
use Windwalker\Database\Test\Driver\AbstractConnectionTest;

/**
 * The MysqlConnectionTest class.
 */
class MysqlConnectionTest extends AbstractConnectionTest
{
    protected static string $platform = 'MySQL';

    protected static string $className = PdoMysqlConnection::class;

    public function assertConnected(AbstractConnection $conn): void
    {
        $pdo = $conn->get();

        $r = $pdo->query('SELECT 1')->fetch(PDO::FETCH_NUM);

        self::assertEquals([1], $r);
    }
}
