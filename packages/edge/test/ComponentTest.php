<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Edge\Component\ComponentExtension;
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeFileLoader;
use Windwalker\Edge\Test\Component\FooComponent;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * The ComponentTest class.
 */
class ComponentTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var Edge
     */
    protected ?Edge $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new Edge(
            new EdgeFileLoader(
                [
                    __DIR__ . '/tmpl',
                ]
            )
        );

        $this->instance->addExtension($ext = new ComponentExtension($this->instance));

        $ext->registerComponent('foo', FooComponent::class);

        // Clear tmp
        $files = glob(__DIR__ . '/../tmp/~*');

        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    public function testComponent()
    {
        $v = $this->instance->render('components.tags');

        show($v);
    }
}
