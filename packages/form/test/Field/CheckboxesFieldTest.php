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
use Windwalker\Dom\Test\AbstractDomTestCase;
use Windwalker\Form\Field\CheckboxesField;
use Windwalker\Html\Option;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class CheckboxesFieldTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var CheckboxesField
     */
    protected CheckboxesField $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new CheckboxesField(
            'flower',
            'Flower',
            [
                'class' => 'stub-flower',
            ]
        );

        $this->instance->addOptions(
            [
                CheckboxesField::createOption('Asia - Tokyo', 'Asia/Tokyo', ['class' => 'opt']),
                CheckboxesField::createOption('Asia - Taipei', 'Asia/Taipei'),
                CheckboxesField::createOption('Europe - Paris', 'Asia/Paris'),
                CheckboxesField::createOption('UTC', 'UTC'),
            ]
        );

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
        $html = <<<HTML
<div id="input-flower" class="stub-flower" data-field-input disabled onchange="return false;" size="10">
    <div id="input-flower-Asia-Tokyo-item" class="checkbox" data-checkbox-item-wrapper>
        <input id="input-flower-Asia-Tokyo" value="Asia/Tokyo" name="flower[]" class="opt" data-checkbox-item-input type="checkbox">
        <label for="input-flower-Asia-Tokyo" id="input-flower-Asia-Tokyo-label" data-checkbox-item-label>Asia - Tokyo</label>
    </div>
    <div id="input-flower-Asia-Taipei-item" class="checkbox" data-checkbox-item-wrapper>
        <input id="input-flower-Asia-Taipei" value="Asia/Taipei" name="flower[]" data-checkbox-item-input type="checkbox">
        <label for="input-flower-Asia-Taipei" id="input-flower-Asia-Taipei-label" data-checkbox-item-label>Asia - Taipei</label>
    </div>
    <div id="input-flower-Asia-Paris-item" class="checkbox" data-checkbox-item-wrapper>
        <input id="input-flower-Asia-Paris" value="Asia/Paris" name="flower[]" data-checkbox-item-input type="checkbox">
        <label for="input-flower-Asia-Paris" id="input-flower-Asia-Paris-label" data-checkbox-item-label>Europe - Paris</label>
    </div>
    <div id="input-flower-UTC-item" class="checkbox" data-checkbox-item-wrapper>
        <input id="input-flower-UTC" value="UTC" name="flower[]" data-checkbox-item-input type="checkbox">
        <label for="input-flower-UTC" id="input-flower-UTC-label" data-checkbox-item-label>UTC</label>
    </div>
</div>
HTML;

        self::assertHtmlFormatEquals($html, $this->instance->renderInput());

        $this->instance->setValue('UTC');

        $html = <<<HTML
<div id="input-flower" class="stub-flower" data-field-input disabled onchange="return false;" size="10">
    <div id="input-flower-Asia-Tokyo-item" class="checkbox" data-checkbox-item-wrapper>
        <input id="input-flower-Asia-Tokyo" value="Asia/Tokyo" name="flower[]" class="opt" data-checkbox-item-input type="checkbox">
        <label for="input-flower-Asia-Tokyo" id="input-flower-Asia-Tokyo-label" data-checkbox-item-label>Asia - Tokyo</label>
    </div>
    <div id="input-flower-Asia-Taipei-item" class="checkbox" data-checkbox-item-wrapper>
        <input id="input-flower-Asia-Taipei" value="Asia/Taipei" name="flower[]" data-checkbox-item-input type="checkbox">
        <label for="input-flower-Asia-Taipei" id="input-flower-Asia-Taipei-label" data-checkbox-item-label>Asia - Taipei</label>
    </div>
    <div id="input-flower-Asia-Paris-item" class="checkbox" data-checkbox-item-wrapper>
        <input id="input-flower-Asia-Paris" value="Asia/Paris" name="flower[]" data-checkbox-item-input type="checkbox">
        <label for="input-flower-Asia-Paris" id="input-flower-Asia-Paris-label" data-checkbox-item-label>Europe - Paris</label>
    </div>
    <div id="input-flower-UTC-item" class="checkbox" data-checkbox-item-wrapper>
        <input id="input-flower-UTC" value="UTC" name="flower[]" checked="checked" data-checkbox-item-input type="checkbox">
        <label for="input-flower-UTC" id="input-flower-UTC-label" data-checkbox-item-label>UTC</label>
    </div>
</div>
HTML;

        self::assertHtmlFormatEquals($html, $this->instance->renderInput());
    }
}
