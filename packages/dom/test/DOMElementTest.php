<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\DOMElement;
use Windwalker\DOM\DOMFactory;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * The DomElementTest class.
 */
class DOMElementTest extends TestCase
{
    use DOMTestTrait;

    /**
     * @var DOMElement
     */
    protected $instance;

    /**
     * @see  DOMElement::create
     */
    public function testCreate(): void
    {
        $ele = DOMElement::create(
            'field',
            [
                'name' => 'foo',
                'label' => 'FOO',
                'class' => function () {
                    return 'col-12 form-control';
                },
                'data-options' => [
                    'handle' => '.handle',
                    'enabled' => true,
                ],
                'data-empty' => '',
                'data-true' => true,
            ]
        );

        self::assertDomStringEqualsDomString(
            '<field name="foo" label="FOO" class="col-12 form-control" ' .
            'data-options="{&quot;handle&quot;:&quot;.handle&quot;,&quot;enabled&quot;:true}"  data-empty="" data-true=""/>',
            $ele
        );

        $ele = DOMElement::create(
            'field',
            [
                'name' => 'foo',
                'label' => 'FOO',
                'class' => function () {
                    return 'col-12 form-control';
                },
                'data-options' => [
                    'handle' => '.handle',
                    'enabled' => true,
                ],
            ],
            DOMElement::create('span', [], 'Hello')
        );

        self::assertDomStringEqualsDomString(
            '<field name="foo" label="FOO" class="col-12 form-control" ' .
            'data-options="{&quot;handle&quot;:&quot;.handle&quot;,&quot;enabled&quot;:true}">' .
            '<span>Hello</span></field>',
            $ele
        );
    }

    /**
     * testCreateWithCSSExpr
     *
     * @param  string  $selector
     * @param  array   $attributes
     * @param  string  $result
     *
     * @return  void
     *
     * @dataProvider cssCreatorProvider
     */
    public function testCreateWithCSSExpr(string $selector, array $attributes, string $result): void
    {
        $ele = DOMElement::create($selector, $attributes);

        self::assertEquals($result, (string) $ele);
    }

    public function cssCreatorProvider(): array
    {
        return [
            [
                'img#foo',
                [],
                '<img id="foo"/>',
            ],
            [
                'img.foo',
                [],
                '<img class="foo"/>',
            ],
            [
                '#foo.bar',
                [],
                '<div id="foo" class="bar"/>',
            ],
            [
                'img#foo.bar.yoo',
                [],
                '<img id="foo" class="bar yoo"/>',
            ],
            [
                'div#foo#foo2.bar.yoo',
                [],
                '<div id="foo2" class="bar yoo"/>',
            ],
            [
                'div#foo.bar',
                [
                    'id' => 'hoo',
                    'class' => 'yoo',
                ],
                '<div id="foo" class="yoo bar"/>',
            ],
        ];
    }

    /**
     * @see  DOMElement::offsetSet
     * @see  DOMElement::offsetGet
     */
    public function testOffsetAccess(): void
    {
        $ele             = DOMElement::create('hello');
        $ele['data-foo'] = 'bar';

        self::assertTrue(isset($ele['data-foo']));
        self::assertEquals('bar', $ele['data-foo']);
        self::assertEquals('<hello data-foo="bar"/>', $ele->render(null));

        unset($ele['data-foo']);

        self::assertEquals('<hello/>', $ele->render(null));
        self::assertFalse(isset($ele['data-foo']));
    }

    /**
     * @see  DOMElement::__toString
     */
    public function testToString(): void
    {
        $ele             = DOMElement::create('hello');
        $ele['data-foo'] = 'bar';

        self::assertEquals('<hello data-foo="bar"/>', (string) $ele);
    }

    /**
     * @see  DOMElement::querySelectorAll
     */
    public function testQuerySelectorAll(): void
    {
        $dom = DOMFactory::create();
        $dom->loadXML(
            <<<XML
<div class="row">
    <div class="col-lg-6 first-col">
        <img src="hello.jpg"/>
    </div>
    <div class="col-lg-6 second-col">
        <img src="flower.jpg"/>
    </div>
    <div class="col-lg-6 third-col">
        <img src="sakura.jpg"/>
    </div>
</div>
XML
        );

        $ele = DOMElement::create(
            'div',
            ['class' => 'root-node'],
            $dom->documentElement
        );

        $imgs = $ele->querySelectorAll('img');

        foreach ($imgs as $img) {
            $images[] = $img['src'];
        }

        self::assertEquals(
            [
                'hello.jpg',
                'flower.jpg',
                'sakura.jpg',
            ],
            $images
        );
    }

    /**
     * @see  DOMElement::querySelector
     */
    public function testQuerySelector(): void
    {
        $dom = DOMFactory::create();
        $dom->loadXML(
            <<<XML
<div class="row">
    <div class="col-lg-6 first-col">
        <img src="hello.jpg"/>
    </div>
    <div class="col-lg-6 second-col">
        <img src="flower.jpg"/>
    </div>
    <div class="col-lg-6 third-col">
        <img src="sakura.jpg"/>
    </div>
</div>
XML
        );

        $ele = DOMElement::create(
            'div',
            ['class' => 'root-node'],
            $dom->documentElement
        );

        $img = $ele->querySelector('img');

        self::assertEquals(1, $img->count());

        self::assertEquals(
            'hello.jpg',
            $img->attr('src')
        );
    }

    /**
     * @see  DOMElement::getName
     */
    public function testGetName(): void
    {
        $ele = DOMElement::create('hello');

        self::assertEquals('hello', $ele->getName());
    }

    /**
     * @see  DOMElement::getAttributes
     */
    public function testGetAttributes(): void
    {
        $ele = DOMElement::create(
            'hello',
            [
                'foo' => 'bar',
                'flower' => 'sakura',
            ]
        );

        $attrs = $ele->getAttributes();

        self::assertEquals('sakura', $attrs['flower']->value);

        $attrs = $ele->getAttributes(true);

        self::assertEquals('sakura', $attrs['flower']);
    }

    /**
     * @see  DOMElement::setAttributes
     */
    public function testSetAttributes(): void
    {
        $ele = DOMElement::create('hello');

        $ele->setAttributes(
            [
                'foo' => 'bar',
                'flower' => 'sakura',
            ]
        );

        self::assertEquals('bar', $ele['foo']);
        self::assertEquals('sakura', $ele['flower']);
    }

    public function testWith(): void
    {
        $dom  = new DOMDocument();
        $root = $dom->createElement('root');

        $ele = DOMElement::create('hello');
        $root->appendChild($ele->with($root));

        self::assertEquals('<root><hello/></root>', $dom->saveXML($root));
    }

    public function testCreateChild(): void
    {
        $ele   = DOMElement::create('root');
        $hello = $ele->createChild('hello');
        $hello->setAttribute('foo', 'bar');

        self::assertEquals('<root><hello foo="bar"/></root>', $ele->render(null));
    }

    public function testBuildAttributes()
    {
        $attrs = DOMElement::buildAttributes(
            [
                'class' => 'foo bar',
                'data-foo' => 'yoo',
                'required' => true,
                'selected' => 'true',
                'disabled' => false,
                'no-show' => null,
                'empty-text' => '',
            ]
        );

        self::assertEquals('class="foo bar" data-foo="yoo" required="" selected="true" empty-text=""', $attrs);
    }

    protected function setUp(): void
    {
        $this->instance = null;
    }

    protected function tearDown(): void
    {
    }
}
