<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Response;

use DOMDocument;
use InvalidArgumentException;
use SimpleXMLElement;

/**
 * The XmlResponse class.
 *
 * @since  3.0
 */
class XmlResponse extends TextResponse
{
    /**
     * Content type.
     *
     * @var  string
     */
    protected string $type = 'application/xml';

    /**
     * Constructor.
     *
     * @param  string  $xml      The XML body data.
     * @param  int     $status   The status code.
     * @param  array   $headers  The custom headers.
     */
    public function __construct($xml = '', $status = 200, array $headers = [])
    {
        parent::__construct(
            $this->toString($xml),
            $status,
            $headers
        );
    }

    /**
     * Convert XML object to string.
     *
     * @param  SimpleXMLElement|DOMDocument|string  $data  XML object or data.
     *
     * @return  string  Converted XML string.
     */
    protected function toString(mixed $data): string
    {
        if ($data instanceof SimpleXMLElement) {
            return $data->asXML();
        }

        if ($data instanceof DOMDocument) {
            return $data->saveXML();
        }

        if (is_string($data)) {
            return $data;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Invalid XML content type, %s provided.',
                gettype($data)
            )
        );
    }
}
