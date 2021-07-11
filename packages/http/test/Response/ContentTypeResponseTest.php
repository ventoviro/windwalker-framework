<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use Windwalker\Http\Response\EmptyResponse;
use Windwalker\Http\Response\HtmlResponse;
use Windwalker\Http\Response\JsonResponse;
use Windwalker\Http\Response\RedirectResponse;
use Windwalker\Http\Response\XmlResponse;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * Test class of EmptyResponse
 *
 * @since 3.0
 */
class ContentTypeResponseTest extends TestCase
{
    use BaseAssertionTrait;

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
     * testOutput
     *
     * @return  void
     */
    public function testHtmlOutput()
    {
        $response = new HtmlResponse('Flower');

        $this->assertEquals('Flower', $response->getBody()->__toString());
        $this->assertEquals(['text/html; charset=utf-8'], $response->getHeader('Content-Type'));
    }

    /**
     * testOutput
     *
     * @return  void
     */
    public function testEmptyOutput()
    {
        $response = new EmptyResponse(204, ['x-foo' => 'bar']);

        $this->assertEquals('', $response->getBody()->__toString());

        // Still read only.
        $response->getBody()->write('Hello');

        $this->assertEquals('', $response->getBody()->__toString());

        $this->assertEquals(['bar'], $response->getHeader('x-foo'));
    }

    /**
     * testOutput
     *
     * @return  void
     */
    public function testJsonOutput()
    {
        $data = [
            'foo' => 'bar',
        ];

        $response = new JsonResponse($data);

        $this->assertEquals(json_encode($data), $response->getBody()->__toString());
        $this->assertEquals(['application/json; charset=utf-8'], $response->getHeader('Content-Type'));

        $response = new JsonResponse('{"foo": 123}');

        $this->assertEquals('{"foo": 123}', $response->getBody()->__toString());
    }

    /**
     * testOutput
     *
     * @return  void
     */
    public function testXmlOutput()
    {
        $xml = new SimpleXMLElement('<root />');
        $child = $xml->addChild('foo', 'bar');
        $child['flower'] = 'sakura';

        $response = new XmlResponse($xml);

        $expected = '<?xml version="1.0"?>
<root><foo flower="sakura">bar</foo></root>';

        $this->assertXmlStringEqualsXmlString($expected, $response->getBody()->__toString());
        $this->assertEquals(['application/xml; charset=utf-8'], $response->getHeader('Content-Type'));

        $xml = new DOMDocument();
        $xml->loadXML("<root />");

        $child = $xml->createElement('foo', 'bar');
        $child->setAttribute('flower', 'sakura');

        $xml->firstChild->appendChild($child);

        $response = new XmlResponse($xml);

        $expected = '<?xml version="1.0"?>
<root><foo flower="sakura">bar</foo></root>';

        $this->assertXmlStringEqualsXmlString($expected, $response->getBody()->__toString());
    }

    /**
     * testRedirectOutput
     *
     * @return  void
     */
    public function testRedirectOutput()
    {
        $response = new RedirectResponse('http://example.com', 307);

        $this->assertEquals(['http://example.com'], $response->getHeader('Location'));
        $this->assertEquals(307, $response->getStatusCode());
    }
}
