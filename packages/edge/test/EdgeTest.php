<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Edge\Cache\EdgeFileCache;
use Windwalker\Edge\Edge;
use Windwalker\Edge\Exception\EdgeException;
use Windwalker\Edge\Loader\EdgeFileLoader;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of Edge
 *
 * @since 3.0
 */
class EdgeTest extends TestCase
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

        // Clear tmp
        // $files = glob(__DIR__ . '/../tmp/~*');
        //
        // foreach ($files as $file) {
        //     unlink($file);
        // }
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
     * @throws EdgeException
     * @covers \Windwalker\Edge\Edge::render
     */
    public function testRender()
    {
        $result = $this->instance->render('hello');

        $expected = <<<HTML
<html>
    <body>
        This is the master sidebar.

        <p>This is appended to the master sidebar.</p>

        <div class="container">
            <p>This is my body content.</p>
            A
        </div>
    </body>
</html>
HTML;

        $this->assertHtmlFormatEquals($expected, $result);
    }

    public function testRenderEdgeFile()
    {
        $result = $this->instance->render(
            'tmpl',
            [
                'test' => '<TEST>',
                'escape' => '<TEST>',
                'yoo' => '<TEST>',
                'a' => array_fill(0, 3, 'Hello'),
            ]
        );

        self::assertStringSafeEquals(
            <<<HTML
<html>
<body>
&lt;TEST&gt;

<TEST>

&lt;TEST&gt;


<li>Hello</li>
<li>Hello</li>
<li>Hello</li>
</body>
</html>
HTML,
            $result
        );
    }

    public function testCachedFile()
    {
        $edge = new Edge(
            new EdgeFileLoader(
                [
                    __DIR__ . '/tmpl',
                ]
            ),
            new EdgeFileCache(
                __DIR__ . '/../tmp'
            )
        );

        $result = $edge->renderWithContext(
            'context-cached',
            [
                'foo' => 'bar',
            ],
            $this
        );

        self::assertStringDataEquals(
            <<<HTML
            Windwalker\Edge\Test\EdgeTest
            HTML,
            $result
        );

        $path = __DIR__ . '/tmpl/context-cached.blade.php';

        self::assertStringDataEquals(
            <<<HTML
            <?php /* File: $path */ ?>
            <?php echo \$__edge->escape(\$this::class); ?>
            HTML,
            file_get_contents(
                __DIR__ . '/../tmp/~'
                . $edge->getCache()->getCacheKey($path)
            )
        );
    }
}
