<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Renderer\Test;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
use Windwalker\Renderer\MustacheRenderer;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of MustacheRenderer
 *
 * @since 2.0
 */
class MustacheRendererTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var MustacheRenderer
     */
    protected MustacheRenderer $instance;

    /**
     * Property path.
     *
     * @var string
     */
    protected static string $path = '';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        static::$path = realpath(__DIR__ . '/Tmpl/mustache');

        if (!static::$path) {
            throw new RuntimeException('Path not exists');
        }

        $this->instance = new MustacheRenderer(['paths' => [static::$path]]);
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

    /**
     * Method to test render().
     *
     * @return void
     *
     * @covers \Windwalker\Renderer\MustacheRenderer::render
     * @throws Throwable
     */
    public function testRender()
    {
        $html = $this->instance->render('hello', ['chris' => new Chris()]);

        $expect = <<<HTML
Hello Chris
You have just won $10000!
Well, $6000, after taxes.
HTML;

        self::assertStringSafeEquals($expect, $html);
    }

    /**
     * Method to test getEngine().
     *
     * @return void
     *
     * @covers \Windwalker\Renderer\MustacheRenderer::getEngine
     * @TODO   Implement testGetEngine().
     */
    public function testGetEngine()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setEngine().
     *
     * @return void
     *
     * @covers \Windwalker\Renderer\MustacheRenderer::setEngine
     * @TODO   Implement testSetEngine().
     */
    public function testSetEngine()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getLoader().
     *
     * @return void
     *
     * @covers \Windwalker\Renderer\MustacheRenderer::getLoader
     * @TODO   Implement testGetLoader().
     */
    public function testGetLoader()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setLoader().
     *
     * @return void
     *
     * @covers \Windwalker\Renderer\MustacheRenderer::setLoader
     * @TODO   Implement testSetLoader().
     */
    public function testSetLoader()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}

// phpcs:disable

/**
 * The Chris class.
 *
 * @since  2.0
 */
class Chris
{
    /**
     * Property name.
     *
     * @var  string
     */
    public $name = "Chris";

    /**
     * Property value.
     *
     * @var  int
     */
    public $value = 10000;

    /**
     * taxed_value
     *
     * @return float|int
     */
    public function taxed_value(): float|int
    {
        return $this->value - ($this->value * 0.4);
    }

    /**
     * Property in_ca.
     *
     * @var  bool
     */
    public $in_ca = true;
}
// phpcs:enable
