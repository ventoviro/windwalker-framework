<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Test;

use Exception;
use Windwalker\Promise\Promise;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The PromiseTest class.
 */
class PromiseTest extends AbstractPromiseTestCase
{
    public function testConstructorAndRun(): void
    {
        $foo = null;

        $p = new Promise(
            function () use (&$foo) {
                $foo = 'Hello';
            }
        );

        self::assertEquals('Hello', $foo);
    }

    public function testConstructorResolve(): void
    {
        // Resolve with value
        $p = new Promise(
            function ($resolve) {
                $resolve('Flower');
            }
        );

        self::assertEquals(Promise::FULFILLED, ReflectAccessor::getValue($p, 'state'));
        self::assertEquals('Flower', ReflectAccessor::getValue($p, 'value'));

        // Resolve with promise
        $p = new Promise(
            function ($resolve) {
                $resolve(
                    new Promise(
                        function ($resolve) {
                            $resolve('Sakura');
                        }
                    )
                );
            }
        );

        self::assertEquals('Sakura', ReflectAccessor::getValue($p, 'value'));
    }

    public function testConstructorCoroutine(): void
    {
        $p = new Promise(
            function ($resolve) use (&$generator) {
                $generator = (static function () use ($resolve) {
                    $resolve(yield);
                })();
            }
        );

        $p->then(
            function ($v) {
                $this->values['v1'] = $v;
            }
        );

        $generator->send('Flower');

        self::assertEquals('Flower', $this->values['v1']);
    }

    public function testRejected(): void
    {
        self::markTestIncomplete();
    }

    public function testRejectedWithoutCatch(): void
    {
        self::markTestSkipped('Enable this after async promise prepared');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Hello');

        Promise::rejected('Hello');
    }
}
