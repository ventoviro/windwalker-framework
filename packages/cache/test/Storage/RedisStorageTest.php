<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use Redis;
use Windwalker\Cache\Storage\RedisStorage;

/**
 * The RedisStorageTest class.
 */
class RedisStorageTest extends AbstractStorageTest
{
    /**
     * @var RedisStorage
     */
    protected $instance;

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        if (!class_exists(Redis::class)) {
            self::markTestSkipped('Redis not supported');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new RedisStorage();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
