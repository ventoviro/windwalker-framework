<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Form\Field\TextField;
use Windwalker\Form\Form;
use Windwalker\Form\Test\Mock\MockFormRenderer;
use Windwalker\Form\Test\Stub\StubFieldDefinition;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * Test class of Form
 *
 * @since 2.0
 */
class FormTest extends TestCase
{
    use BaseAssertionTrait;

    /**
     * Test instance.
     *
     * @var Form
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
    }

    /**
     * getByDefine
     *
     * @param  string  $ns
     *
     * @return  Form
     */
    protected function getByDefine($ns = ''): Form
    {
        $form = new Form($ns);

        $form->defineFormFields(new StubFieldDefinition());

        return $form;
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
     * Method to test addFields().
     *
     * @return void
     */
    public function testAddAndGetFields()
    {
        $form = new Form();

        $form->addFields(
            [
                new TextField('foo'),
                new TextField('bar'),
            ]
        );

        $fields = iterator_to_array($form->getFields());

        self::assertInstanceOf('Windwalker\\Form\\Field\\TextField', $fields['foo']);
        self::assertEquals('bar', $fields['bar']->getName());

        // Test fieldset
        $form->addFields(
            [
                new TextField('bird'),
                new TextField('rabbit'),
            ],
            'flower'
        );

        $fields = iterator_to_array($form->getFields('flower'));

        self::assertInstanceOf(TextField::class, $fields['bird']);
        self::assertEquals('rabbit', $fields['rabbit']->getName());

        // Test Group
        $form->addFields(
            [
                new TextField('egg'),
                new TextField('hotdog'),
            ],
            'rose',
            'sakura'
        );

        $fields = iterator_to_array($form->getFields(null, 'sakura'));

        self::assertInstanceOf(TextField::class, $fields['sakura/egg']);
        self::assertEquals('sakura[hotdog]', $fields['sakura/hotdog']->getInputName());
    }

    /**
     * Method to test addField().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::addField
     */
    public function testAddAndGetField()
    {
        $form = new Form();

        $form->addField(new TextField('foo'));

        self::assertEquals('foo', $form->getField('foo')->getInputName());

        $form->addField(new TextField('bar'), 'flower');

        self::assertEquals('bar', $form->getField('bar')->getInputName());

        $form->addField(new TextField('yoo'), null, 'sakura');

        self::assertEquals('sakura[yoo]', $form->getField('sakura/yoo')->getInputName());
    }

    /**
     * Method to test getIterator().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::getIterator
     */
    public function testGetIterator()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test removeField().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::removeField
     */
    public function testRemoveField()
    {
        $form = $this->getByDefine();

        $form->removeField('u/username');

        self::assertNull($form->getField('u/username'));
    }

    /**
     * Method to test removeFields().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::removeFields
     */
    public function testRemoveFields()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getFieldsets().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::getFieldsets
     * @TODO   Implement testGetFieldsets().
     */
    public function testGetFieldsets()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getGroups().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::getGroups
     * @TODO   Implement testGetGroups().
     */
    public function testGetGroups()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setAttribute().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::setAttribute
     * @TODO   Implement testSetAttribute().
     */
    public function testSetAttribute()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getAttribute().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::getAttribute
     * @TODO   Implement testGetAttribute().
     */
    public function testGetAttribute()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test fill().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::fill
     */
    public function testFill()
    {
        $data = [
            'id' => 5,
            'u' => [
                'username' => 'foo',
            ],
        ];

        $form = $this->getByDefine();

        $fields = iterator_to_array($form->bind($data)->getFields());

        self::assertEquals(5, $fields['id']->getValue());
        self::assertEquals('foo', $fields['u/username']->getValue());
    }

    public function testBind()
    {
        $data = [
            'id' => 5,
            'u' => [
                'username' => 'foo',
            ],
        ];

        $form = $this->getByDefine();

        $fields = iterator_to_array($form->bind($data)->getFields());

        $data['id'] = 6;
        $data['u']['username'] = 'bar';

        self::assertEquals(6, $fields['id']->getValue());
        self::assertEquals('bar', $fields['u/username']->getValue());
    }

    /**
     * Method to test validate().
     *
     * @return void
     */
    public function testValidate()
    {
        $form = $this->getByDefine();

        $data = [
            'id' => 5,
            'u' => [
                'username' => 'foo',
            ],
            'b' => [
                'email' => 'bar@gmail.com',
                'password' => '123_ abc4456qwe:$yui',
            ],
        ];

        $form->bind($data);

        $result = $form->validate($form->filter($data));

        self::assertTrue($result->isSuccess());

        $data = [
            'id' => 5,
            'u' => [
                'username' => 'foo',
            ],
            'b' => [
                'email' => 'bar/gmail.com',
                'password' => '123_ abc4456qwe:$yui',
            ],
        ];

        $result = $form->validate($filtered = $form->filter($data));

        self::assertTrue($result->isFailure());
        self::assertTrue($result->getResult('u/username')->isSuccess());

        // Filter ALNUM
        self::assertEquals('123abc4456qweyui', $filtered['b']['password']);
    }

    /**
     * Method to test prepareView().
     *
     * @return void
     */
    public function testGetViews()
    {
        $form = $this->getByDefine();

        $data = [
            'id' => 5,
            'u' => [
                'username' => 'foo',
            ],
            'b' => [
                'email' => 'bar/gmail.com',
                'password' => '123_ abc4456qwe:$yui',
            ],
        ];

        $form->bind($data);

        $array = $form->getViews();

        self::assertEquals('123_ abc4456qwe:$yui', $array['Password']);
    }

    /**
     * Method to test prepareStore().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::prepareStore
     */
    public function testPrepareStore()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test renderField().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::renderField
     *
     * @since  2.1.2
     */
    public function testRenderField()
    {
        $form = $this->getByDefine('windwalker');

        $html = '<div id="input-windwalker-id-wrapper" class="control-input" data-field-wrapper><label id="input-windwalker-id-label" data-field-label for="input-windwalker-id">ID</label><div><input id="input-windwalker-id" name="windwalker[id]" data-field-input type="text" value></div></div>';

        self::assertEquals($html, $form->renderField('id'));

        // Use renderer

        $form->setRenderer(new MockFormRenderer());

        self::assertEquals(
            '<mock id="input-windwalker-id-wrapper" class="control-input" data-field-wrapper>Hello World: windwalker[id]</mock>',
            $form->renderField('id')
        );
    }

    /**
     * Method to test renderFields().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::renderFields
     * @TODO   Implement testRenderFields().
     */
    public function testRenderFields()
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getControl().
     *
     * @return void
     */
    public function testGetAndSetNamespace()
    {
        $form = $this->getByDefine();

        $form->setNamespace('wind');

        self::assertEquals('wind[id]', $form->getField('id')->getInputName());
        self::assertEquals('wind', $form->getNamespace());
    }

    /**
     * testFilterAndGetValues
     *
     * @return  void
     *
     * @covers \Windwalker\Form\Form::filter
     * @covers \Windwalker\Form\Form::getValues
     */
    public function testFilterAndGetValues()
    {
        $form = $this->getByDefine();

        $data = [
            'id' => '123abc',
            'u' => [
                'username' => 'foo',
            ],
            'b' => [
                'email' => 'bar@gmail.com',
                'password' => '123_ abc/\4,456qwe:$yui',
            ],
        ];

        $filtered = $form->filter($data);

        self::assertEquals('123', $filtered['id']);
        self::assertEquals('123abc4456qweyui', $filtered['b']['password']);
    }

    /**
     * testSetAndGetFieldRendererHandler
     *
     * @return  void
     */
    public function testSetAndGetFieldRendererHandler()
    {
        $renderer = new MockFormRenderer();

        $form = new Form();
        $form->add('test', new TextField());

        $form->setRenderer($renderer);

        self::assertEquals(
            '<mock id="input-test-wrapper" data-field-wrapper>Hello World: test</mock>',
            trim($form->renderFields())
        );
    }
}
