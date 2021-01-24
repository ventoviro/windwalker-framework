<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use Memcached;
use Windwalker\Cache\Storage\MemcachedStorage;

/**
 * The MemcachedStorageTest class.
 */
class MemcachedStorageTest extends AbstractStorageTest
{
    /**
     * @var MemcachedStorage
     */
    protected $instance;

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        if (!class_exists(Memcached::class)) {
            self::markTestSkipped('Memcached not supported');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new MemcachedStorage();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
