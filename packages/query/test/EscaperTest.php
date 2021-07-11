<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Query\Escaper;

/**
 * The EscaperTest class.
 */
class EscaperTest extends TestCase
{
    /**
     * @var Escaper
     */
    protected $instance;

    /**
     * @see  Escaper::tryQuote
     */
    public function testQuote(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Escaper::stripQuote
     */
    public function testStripQuote(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Escaper::tryEscape
     */
    public function testEscape(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->instance = null;
    }

    protected function tearDown(): void
    {
    }
}
