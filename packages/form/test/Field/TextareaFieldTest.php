<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Test\Field;

use PHPUnit\Framework\TestCase;
use Windwalker\Form\Field\TextareaField;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class TextareaFieldTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var TextareaField
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
        $this->instance = new TextareaField(
            'flower',
            'Flower',
            [
                'class' => 'stub-flower',
            ]
        );

        $this->instance->setAttribute('id', 'test-field');
        $this->instance->setAttribute('readonly', true);
        $this->instance->setAttribute('disabled', true);
        $this->instance->setAttribute('onchange', 'void(0);');
        $this->instance->setAttribute('cols', 10);
        $this->instance->rows(15);

        $this->instance->setValue('sakura');
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
     */
    public function testRender()
    {
        // phpcs:disable
        $html = <<<HTML
<textarea id="test-field" name="flower" class="stub-flower" cols="10" data-field-input disabled onchange="void(0);" readonly rows="15">sakura</textarea>
HTML;
        // phpcs:enable

        self::assertDomStringEqualsDomString($html, $this->instance->renderInput());
    }
}
