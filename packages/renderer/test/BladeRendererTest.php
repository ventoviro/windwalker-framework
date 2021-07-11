<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Renderer\Test;

use Illuminate\Contracts\View\Factory;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Filesystem\Filesystem;
use Windwalker\Renderer\BladeRenderer;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of BladeRenderer
 *
 * @since 2.0
 */
class BladeRendererTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var BladeRenderer
     */
    protected $instance;

    /**
     * Property path.
     *
     * @var string
     */
    protected static $path;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        static::$path = realpath(__DIR__ . '/Tmpl/blade');

        if (!static::$path) {
            throw new RuntimeException('Path not exists');
        }

        Filesystem::mkdir(__DIR__ . '/cache');

        $this->instance = new BladeRenderer(
            [
                'cache_path' => __DIR__ . '/cache',
                'paths' => [static::$path],
            ]
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Filesystem::delete(__DIR__ . '/cache');
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        Filesystem::delete(__DIR__ . '/cache');
    }

    /**
     * Method to test render().
     *
     * @return void
     */
    public function testRender()
    {
        $html = $this->instance->make('hello')();

        $expect = <<<HTML
<html>
<body>
    This is the master sidebar.

    <p>This is appended to the master sidebar.</p>
    <div class="container">
        <p>This is my body content.</p>
    </div>
</body>
</html>
HTML;

        self::assertHtmlFormatEquals($expect, $html);
    }

    /**
     * Method to test getBlade().
     *
     * @return void
     */
    public function testGetBlade()
    {
        self::assertInstanceOf(Factory::class, $this->instance->createEngine([]));
    }
}
