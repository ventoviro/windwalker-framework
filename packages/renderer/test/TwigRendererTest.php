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
use Windwalker\Dom\Test\AbstractDomTestCase;
use Windwalker\Renderer\TwigRenderer;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of TwigRenderer
 *
 * @since 2.0
 */
class TwigRendererTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Property path.
     *
     * @var string
     */
    protected static $path;

    /**
     * Test instance.
     *
     * @var TwigRenderer
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        static::$path = realpath(__DIR__ . '/Tmpl/twig');

        if (!static::$path) {
            throw new RuntimeException('Path not exists');
        }

        $this->instance = new TwigRenderer(['paths' => static::$path]);
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
     * @covers \Windwalker\Renderer\TwigRenderer::render
     */
    public function testRender()
    {
        $html = $this->instance->render('default.twig');

        $expect = <<<HTML
<div id="global">
    <p> (_global/global) Lorem ipsum dolor sit amet</p>
    <p> (default) Nulla sed libero sem. Praesent ac dignissim risus.</p>
    <p> (foo/bar) Phasellus vitae bibendum neque, quis suscipit urna. Fusce eu odio ante.</p>
    <p> (_global/global) Suspendisse finibus fermentum massa ut tempus. </p>
</div>
HTML;

        self::assertDomStringEqualsDomString($expect, $html);
    }
}
