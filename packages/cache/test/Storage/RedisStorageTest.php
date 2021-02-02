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
use Windwalker\Utilities\Env;

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
        if (!Env::get('REDIS_ENABLED')) {
            self::markTestSkipped('Redis not enabled');
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
