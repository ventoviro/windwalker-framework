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
use Windwalker\Renderer\CompositeRenderer;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * The CompositeRendererTest class.
 */
class CompositeRendererTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?CompositeRenderer $instance;

    public function testBlade()
    {
        $r = $this->instance->make('foo.olive', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'Olive Welcome',
            $r
        );
    }

    public function testTwig()
    {
        $r = $this->instance->make('sunflower', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'SUNFLOWER Welcome',
            $r
        );

        $r = $this->instance->make('foo.sunflower', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'Sunflower Welcome',
            $r
        );
    }

    public function testTwigWithExtension()
    {
        $r = $this->instance->make('sunflower.twig', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'SUNFLOWER Welcome',
            $r
        );

        $r = $this->instance->make('foo/sunflower.twig', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'Sunflower Welcome',
            $r
        );
    }

    public function testMustache()
    {
        $r = $this->instance->make('foo.rose', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'ROSE Welcome',
            $r
        );

        $r = $this->instance->make('rose.mustache', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'Rose Welcome',
            $r
        );
    }

    /**
     * @see  CompositeRenderer::make
     */
    public function testMake(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CompositeRenderer::render
     */
    public function testRender(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CompositeRenderer::findFile
     */
    public function testFindFile(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->instance = new CompositeRenderer(
            [
                __DIR__ . '/Tmpl/mixed',
                __DIR__ . '/Tmpl/mixed2',
            ],
            [
                'cache_path' => __DIR__ . '/../tmp',
            ]
        );
    }

    protected function tearDown(): void
    {
    }
}
