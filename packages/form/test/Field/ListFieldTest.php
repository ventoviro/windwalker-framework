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
use Windwalker\DOM\HTMLFactory;
use Windwalker\Form\Field\ListField;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class ListFieldTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var ListField
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
        $this->instance = new ListField(
            'flower',
            'Flower'
        );
        $this->instance->addClass('stub-flower');
        $this->instance->option('', '');

        $this->instance->option('Yes', '1')
            ->option('No', '0');

        $this->instance->setAttribute('size', 10);
        $this->instance->setAttribute('readonly', false);
        $this->instance->setAttribute('disabled', true);
        $this->instance->setAttribute('onchange', 'return false;');
        $this->instance->setAttribute('multiple', false);
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
<select id="input-flower" name="flower" class="stub-flower" data-field-input disabled onchange="return false;" size="10">
    <option value selected="selected"></option>
    <option value="1">Yes</option>
    <option value="0">No</option>
</select>
HTML;

        self::assertDomFormatEquals($html, $this->instance->renderInput());

        $this->instance->setValue(1);

        $html = <<<HTML
<select id="input-flower" name="flower" class="stub-flower" data-field-input disabled onchange="return false;" size="10">
    <option value></option>
    <option value="1" selected="selected">Yes</option>
    <option value="0">No</option>
</select>
HTML;

        self::assertDomFormatEquals($html, $this->instance->renderInput());

        $this->instance->setAttribute('multiple', true);

        $html = <<<HTML
<select id="input-flower" name="flower[]" class="stub-flower" data-field-input disabled multiple onchange="return false;" size="10">
    <option value></option>
    <option value="1" selected="selected">Yes</option>
    <option value="0">No</option>
</select>
HTML;

        self::assertDomFormatEquals($html, $this->instance->renderInput());
        // php:disable
    }

    /**
     * Method to test prepareAttributes().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\TextField::prepareAttributes
     */
    public function testRenderGroup()
    {
        $field = new ListField(
            'timezone',
            'Time Zone'
        );

        $field->setOptions(
            [
                'Asia' => [
                    HTMLFactory::option(['value' => 'Asia/Tokyo', 'class' => 'opt'], 'Tokyo'),
                    HTMLFactory::option(['value' => 'Asia/Taipei'], 'Taipei'),
                ],
            ]
        );

        $field->group(
            'Europe',
            function (ListField $field) {
                $field->option('Paris', 'Europe/Paris');
            }
        )->option('UTC', 'UTC');

        $html = <<<HTML
<select id="input-timezone" name="timezone" data-field-input>
    <optgroup label="Asia">
        <option value="Asia/Tokyo" class="opt">Tokyo</option>
        <option value="Asia/Taipei">Taipei</option>
    </optgroup>

    <optgroup label="Europe">
        <option value="Europe/Paris">Paris</option>
    </optgroup>

    <option value="UTC">UTC</option>
</select>
HTML;

        self::assertDomStringEqualsDomString($html, $field->renderInput());
    }
}
