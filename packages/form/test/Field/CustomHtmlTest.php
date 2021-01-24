<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Test\Field;

use Exception;
use PHPUnit\Framework\TestCase;
use Windwalker\Dom\Test\AbstractDomTestCase;
use Windwalker\Form\Field\CustomHtmlField;
use Windwalker\Test\Traits\DOMTestTrait;

use function Windwalker\DOM\h;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class CustomHtmlTest extends TestCase
{
    use DOMTestTrait;

    protected CustomHtmlField $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new CustomHtmlField(
            'flower',
            'Flower'
        );

        $this->instance->setAttribute('content', h('div', ['data-test-element' => true], 'Sakura'));
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
     * Method to test prepareAttributes().
     *
     * @return void
     * @throws Exception
     */
    public function testRender()
    {
        $html = <<<HTML
<div data-test-element>Sakura</div>
HTML;

        self::assertHtmlFormatEquals($html, $this->instance->renderInput());
    }
}
