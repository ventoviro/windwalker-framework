<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Test\Failer;

use PHPUnit\Framework\TestCase;
use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\Queue\Failer\DatabaseQueueFailer;
use Windwalker\Queue\Failer\PdoQueueFailer;

/**
 * The DatabaseQueueFailerTest class.
 */
class PdoQueueFailerTest extends DatabaseQueueFailerTest
{
    protected function setUp(): void
    {
        $conn = self::$db->getDriver()->getConnection();

        $this->instance = new PdoQueueFailer(
            $conn->get()
        );

        $conn->release();
    }

    protected function tearDown(): void
    {
    }
}
