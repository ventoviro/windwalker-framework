<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Test\Injection;

/**
 * The StubService class.
 *
 * @since  3.4.4
 */
class StubService
{
    /**
     * Property counter.
     *
     * @var  int
     */
    public static $counter = 0;

    /**
     * StubService constructor.
     */
    public function __construct()
    {
        static::$counter++;
    }

    /**
     * run
     *
     * @return  string
     *
     * @since  3.4.4
     */
    public function run(): string
    {
        return 'OK';
    }

    /**
     * getCounter
     *
     * @return  int
     *
     * @since  3.4.4
     */
    public function getCounter(): int
    {
        return static::$counter;
    }
}
