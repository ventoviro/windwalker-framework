<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pgsql;

use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Driver\Pgsql\PgsqlConnection;
use Windwalker\Database\Test\Driver\AbstractConnectionTest;

/**
 * The PgsqlConnectionTest class.
 */
class PgsqlConnectionTest extends AbstractConnectionTest
{
    protected static string $platform = 'PostgreSQL';

    protected static string $className = PgsqlConnection::class;

    /**
     * @inheritDoc
     */
    public function assertConnected(AbstractConnection $conn): void
    {
        $cursor = pg_query($conn->get(), 'SELECT 1');

        $r = pg_fetch_result($cursor, 0);

        self::assertEquals(1, $r);
    }
}
