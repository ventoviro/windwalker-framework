<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Response;

use Psr\Http\Message\StreamInterface;

/**
 * An response object contains content-type handler.
 *
 * @since  3.0
 */
abstract class AbstractContentTypeResponse extends Response
{
    /**
     * Content type.
     *
     * @var  string
     */
    protected $type = 'text/plain';

    /**
     * Constructor.
     *
     * @param  string $body    The body data.
     * @param  int    $status  The status code.
     * @param  array  $headers The custom headers.
     */
    public function __construct($body = '', $status = 200, array $headers = [])
    {
        parent::__construct(
            $this->handleBody($body),
            $status,
            $this->addContentTypeToHeader($headers, $this->type . '; charset=utf-8')
        );
    }

    /**
     * Handle body to stream object.
     *
     * @param  string  $body  The body data.
     *
     * @return  StreamInterface  Converted to stream object.
     */
    abstract protected function handleBody(string $body): StreamInterface;

    /**
     * withContent
     *
     * @param  string  $content
     *
     * @return  static
     * @throws \InvalidArgumentException
     */
    public function withContent(string $content): static
    {
        return $this->withBody($this->handleBody($content));
    }

    /**
     * Add Content-Type to header.
     *
     * @param  string  $contentType  The content type.
     *
     * @return  static
     */
    public function withContentType(string $contentType): static
    {
        $contentType = $this->normalizeContentType($contentType);

        $contentType = explode(';', $contentType, 2);

        $this->type = $contentType[0];

        $contentType[0] .= ';' . (isset($contentType[1]) ? $contentType[1] : ' charset=utf-8');

        return $this->withHeader('Content-Type', $contentType[0]);
    }

    /**
     * Add content-type to headers variable if not exists.
     *
     * @param  array   $headers      The headers variable.
     * @param  string  $contentType  The content-type.
     *
     * @return array
     */
    protected function addContentTypeToHeader(array $headers, string $contentType): array
    {
        $keys = array_change_key_case(array_keys($headers), CASE_LOWER);

        if (!isset($keys['content-type'])) {
            $headers['content-type'] = [$contentType];
        }

        return $headers;
    }

    /**
     * Normalize content-type.
     *
     * @param  string  $contentType  Content-type string.
     *
     * @return  string
     */
    protected function normalizeContentType(string $contentType): string
    {
        return strtolower((string) $contentType);
    }
}
