<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\DI\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\DI\Parameters;

use function Windwalker\ref;

/**
 * The ParametersTest class.
 */
class ParametersTest extends TestCase
{
    protected ?Parameters $instance;

    /**
     * @see  Parameters::extract
     */
    public function testExtract(): void
    {
        $foo = $this->instance->extract('foo', true);

        self::assertEquals(
            'Hello',
            $foo->getDeep('bar.yoo')
        );

        self::assertEquals(
            'Hello',
            $foo->getDeep('foo.bar.yoo')
        );
    }

    public function testGetRefSelf()
    {
        self::assertEquals(
            'Hello',
            $this->instance->getDeep('ref')
        );

        $this->instance->set('ref', ref('foo/bar/yoo', '/'));

        self::assertEquals(
            'Hello',
            $this->instance->getDeep('ref')
        );
    }

    public function testGetRefParent()
    {
        $foo = $this->instance->extract('foo', true);

        self::assertEquals(
            'Hello',
            $foo->getDeep('ref')
        );

        $foo->set('ref', ref('bar.yoo'));

        self::assertEquals(
            'Hello',
            $foo->getDeep('ref')
        );
    }

    /**
     * @see  Parameters::hasDeep
     */
    public function testHasDeep(): void
    {
        $foo = $this->instance->extract('foo', true);

        self::assertTrue($foo->hasDeep('bar.yoo'));
        self::assertTrue($foo->hasDeep('foo.bar.yoo'));
        self::assertFalse($foo->hasDeep('foo.goo'));
    }

    /**
     * @see  Parameters::has
     */
    public function testHas(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Parameters::get
     */
    public function testGet(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Parameters::getParent
     */
    public function testGetParent(): void
    {
        $foo = $this->instance->extract('foo', true);

        self::assertSame($this->instance, $foo->getParent());
    }

    protected function setUp(): void
    {
        $this->instance = new Parameters(
            [
                'foo' => [
                    'bar' => [
                        'yoo' => 'Hello',
                    ],
                ],
                'ref' => ref('foo.bar.yoo'),
            ]
        );
    }

    protected function tearDown(): void
    {
    }
}
