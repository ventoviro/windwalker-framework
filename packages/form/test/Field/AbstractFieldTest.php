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
use Windwalker\Filter\Rule\IPAddress;
use Windwalker\Filter\Rule\Regex;
use Windwalker\Form\Test\Mock\MockFilter;
use Windwalker\Form\Test\Stub\StubField;
use Windwalker\Form\Test\Stub\StubFilter;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of AbstractField
 *
 * @since 2.0
 */
class AbstractFieldTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var StubField
     */
    protected StubField $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new StubField(
            'flower',
            'Flower',
            [
                'placeholder' => 'The Flower',
                'class' => 'stub-flower',
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
    }

    /**
     * Method to test renderInput().
     *
     * @return void
     */
    public function testRenderInput()
    {
        // phpcs:disable
        $expect = <<<HTML
<input id="input-flower" name="flower" class="stub-flower" data-field-input placeholder="The Flower" type="text">
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());

        // Control
        $this->instance->setNamespace('windwalker');

        $expect = <<<HTML
<input id="input-windwalker-flower" name="windwalker[flower]" class="stub-flower" data-field-input placeholder="The Flower" type="text">
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());

        // Default value
        $this->instance->set('default', 'default-value');

        $expect = <<<HTML
<input id="input-windwalker-flower" name="windwalker[flower]" class="stub-flower" data-field-input placeholder="The Flower" type="text" value="default-value">
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());

        // Value
        $this->instance->setValue('sakura');

        $expect = <<<HTML
<input id="input-windwalker-flower" name="windwalker[flower]" class="stub-flower" data-field-input placeholder="The Flower" type="text" value="sakura">
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());

        // Group
        $this->instance->appendNamespace('foo/bar');

        $expect = <<<HTML
<input id="input-windwalker-foo-bar-flower" name="windwalker[foo][bar][flower]" class="stub-flower" data-field-input placeholder="The Flower" type="text" value="sakura">
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());
        // phpcs:enable
    }

    /**
     * Method to test buildInput().
     *
     * @return void
     */
    public function testBuildInput()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test renderLabel().
     *
     * @return void
     */
    public function testRenderLabel()
    {
        $expect = <<<HTML
<label id="input-flower-label" data-field-label for="input-flower">Flower</label>
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderLabel());
    }

    /**
     * Method to test renderView().
     *
     * @return void
     */
    public function testRenderView()
    {
        $this->instance->setValue('sakura');

        self::assertEquals('sakura', $this->instance->renderView());
    }

    /**
     * Method to test render().
     *
     * @return void
     */
    public function testRender()
    {
        $this->instance->setNamespace('windwalker');
        $this->instance->setValue('sakura');

        $this->instance->addWrapperClass('form-group');
        $this->instance->setAttribute('data-test-element', true);

        $expect = <<<HTML
<div id="input-windwalker-flower-wrapper" class="form-group" data-field-wrapper>
    <label id="input-windwalker-flower-label" data-field-label for="input-windwalker-flower">Flower</label>
    <div>
        <input id="input-windwalker-flower" name="windwalker[flower]" class="stub-flower" data-field-input data-test-element placeholder="The Flower" type="text" value="sakura">
    </div>
</div>
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->render());

        $expect = <<<HTML
<div id="input-windwalker-flower-wrapper" class="form-group" data-field-wrapper>
    <div>
        <input id="input-windwalker-flower" name="windwalker[flower]" class="stub-flower" data-field-input data-test-element placeholder="The Flower" type="text" value="sakura">
    </div>
</div>
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->render(['no_label' => true]));
    }

    /**
     * Method to test getLabel().
     *
     * @return void
     */
    public function testGetLabelName()
    {
        self::assertEquals('Flower', $this->instance->getLabelName());
    }

    /**
     * Method to test getId().
     *
     * @return void
     */
    public function testGetId()
    {
        self::assertEquals('input-flower', $this->instance->getId());

        $this->instance->setNamespace('windwalker');

        self::assertEquals('input-windwalker-flower', $this->instance->getId());

        $this->instance->setName('a.b-c_d:e');

        self::assertEquals('input-windwalker-a-b-c_d-e', $this->instance->getId());
    }

    /**
     * Method to test validate().
     *
     * @return void
     */
    public function testValidate()
    {
        $field = StubField::create(
            'flower',
            'Flower',
            [
                'placeholder' => 'The Flower',
                'class' => 'stub-flower',
            ],
            null
        )
            ->addValidator(IPAddress::class);

        // No value will not validate
        self::assertTrue($field->validate(null)->isSuccess());

        // Do validate
        self::assertTrue($field->validate('123.21.23.156')->isSuccess());

        self::assertTrue($field->validate('/var/foo/bar')->isFailure());
    }

    /**
     * Method to test checkRequired().
     *
     * @return void
     */
    public function testCheckRequired()
    {
        $field = new StubField(
            'flower',
            'Flower',
            [
                'placeholder' => 'The Flower',
                'class' => 'stub-flower',
                'required' => true,
            ]
        );

        self::assertTrue($field->validate(null)->isFailure());

        self::assertTrue($field->validate('123')->isSuccess());
    }

    /**
     * Method to test checkRule().
     *
     * @return void
     */
    public function testCheckRule()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test filter().
     *
     * @return void
     */
    public function testFilter()
    {
        $field = StubField::create(
            'flower',
            'Flower',
            [
                'placeholder' => 'The Flower',
                'class' => 'stub-flower',
            ]
        )
            ->addFilter('cmd|func(strtoupper)');

        self::assertEquals('ABCFOO_BAR-YOODIVDATADIV456789', $field->filter('abc foo_bar-yoo<div>data</div>456:789'));

        $field->addFilter(new MockFilter())->setValue('foo');

        self::assertEquals('abc', $field->filter('foo'));
    }

    /**
     * Method to test getName().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::getName
     */
    public function testGetName()
    {
        $this->instance->setControl('windwalker');
        $this->instance->setValue('sakura');

        self::assertEquals('flower', $this->instance->getName());
    }

    /**
     * Method to test setName().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::setName
     */
    public function testSetName()
    {
        $this->instance->setNamespace('windwalker');
        $this->instance->appendNamespace('goo');
        $this->instance->setName('yoo');

        self::assertEquals('input-windwalker-goo-yoo', $this->instance->getId());
    }

    /**
     * Method to test getFieldName().
     *
     * @return void
     */
    public function testGetFullName()
    {
        $this->instance->setNamespace('windwalker');

        self::assertEquals('windwalker[flower]', $this->instance->getInputName());

        $this->instance->appendNamespace('foo/bar');
        $this->instance->setName('yoo');

        self::assertEquals('windwalker[foo][bar][yoo]', $this->instance->getInputName());
    }

    /**
     * Method to test setFieldName().
     *
     * @return void
     */
    public function testSetFieldName()
    {
        $this->instance->setAttribute('name', 'foo[bar]');

        self::assertEquals('foo[bar]', $this->instance->getInputName());
    }

    /**
     * Method to test getGroup().
     *
     * @return void
     */
    public function testGetAbdSetGroup()
    {
        $this->instance->appendNamespace('wind');

        // phpcs:disable
        $expect = <<<HTML
<input id="input-wind-flower" name="wind[flower]" class="stub-flower" data-field-input placeholder="The Flower" type="text">
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());

        $this->instance->setNamespace('wind/walker');

        $expect = <<<HTML
<input id="input-wind-walker-flower" name="wind[walker][flower]" class="stub-flower" data-field-input placeholder="The Flower" type="text">
HTML;

        // phpcs:enable
        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());
    }

    /**
     * Method to test getFieldset().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::getFieldset
     * @TODO   Implement testGetFieldset().
     */
    public function testGetFieldset()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setFieldset().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::setFieldset
     * @TODO   Implement testSetFieldset().
     */
    public function testSetFieldset()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getValue().
     *
     * @return void
     */
    public function testGetAndSetValue()
    {
        // Test default value
        $this->instance->defaultValue('joo');

        self::assertEquals('joo', $this->instance->getValue());

        // Test set value
        $this->instance->setValue('Sakura');

        self::assertEquals('Sakura', $this->instance->getValue());
    }

    /**
     * Method to test setFilter().
     *
     * @return void
     */
    public function testGetAndSetFilter()
    {
        // Set filter type
        $this->instance->resetFilters()->addFilter(new Regex('/[^0-9]+/'));

        self::assertEquals('123', $this->instance->filter('abc123cba'));

        // Set filter handler
        $closure = static fn($value) => filter_var($value, FILTER_SANITIZE_NUMBER_INT);

        $this->instance->resetFilters()->addFilter($closure);

        self::assertEquals('123123', $this->instance->filter('abc123cba123fgh'));

        // Set filter object
        $this->instance->resetFilters()->addFilter(new StubFilter());

        self::assertEquals('123123', $this->instance->filter('abc123cba123fgh'));
    }

    /**
     * Method to test getControl().
     *
     * @return void
     */
    public function testGetControl()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setControl().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::setControl
     * @TODO   Implement testSetControl().
     */
    public function testSetControl()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * testAddClass
     *
     * @return  void
     */
    public function testAddClass()
    {
        $this->instance->addClass('foo');

        self::assertEquals('stub-flower foo', $this->instance->getAttribute('class'));

        $this->instance->addClass('stub-flower bar');

        self::assertEquals('stub-flower foo bar', $this->instance->getAttribute('class'));

        $this->instance->addClass('yoo', 'goo');

        self::assertEquals('stub-flower foo bar yoo goo', $this->instance->getAttribute('class'));
    }

    /**
     * testRemoveClass
     *
     * @return  void
     */
    public function testRemoveClass()
    {
        $this->instance->addClass('foo bar yoo goo');

        $this->instance->removeClass('bar');

        self::assertEquals('stub-flower foo yoo goo', $this->instance->getAttribute('class'));

        $this->instance->removeClass('yoo goo');

        self::assertEquals('stub-flower foo', $this->instance->getAttribute('class'));

        $this->instance->removeClass('foo', 'yoo');

        self::assertEquals('stub-flower', $this->instance->getClass());
    }

    /**
     * Method to test getAttribute().
     *
     * @return void
     */
    public function testGetAttribute()
    {
        $field = new StubField(
            'flower',
            'Flower',
            [
                'placeholder' => 'The Flower',
                'class' => 'stub-flower',
                'required' => true,
            ]
        );

        self::assertEquals('stub-flower', $field->getAttribute('class'));
        self::assertEquals('default', $field->getAttribute('host') ?? 'default');
        self::assertEquals(null, $field->getAttribute('host'));
    }

    /**
     * Method to test setAttribute().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::setAttribute
     */
    public function testSetAttribute()
    {
        $this->instance->setAttribute('host', 'localhost');

        self::assertEquals('localhost', $this->instance->getAttribute('host', 'default'));
    }

    /**
     * Method to test get().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::get
     */
    public function testGet()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getAttributes().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::getAttributes
     */
    public function testGetAttributes()
    {
        self::assertEquals(
            ['placeholder' => 'The Flower', 'class' => 'stub-flower'],
            $this->instance->getAttributes()
        );
    }
}
