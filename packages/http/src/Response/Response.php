<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Http\Helper\ResponseHelper;
use Windwalker\Http\MessageTrait;
use Windwalker\Stream\Stream;

/**
 * The AbstractResponse class.
 *
 * @since  2.1
 */
class Response implements ResponseInterface
{
    use MessageTrait;

    /**
     * Property statusCode.
     *
     * @var  int
     */
    protected int $statusCode = 200;

    /**
     * Property reasonPhrase.
     *
     * @var  string
     */
    protected ?string $reasonPhrase = null;

    /**
     * create
     *
     * @param  int                     $status
     * @param  string  $body
     * @param  array                   $headers
     *
     * @return  static
     */
    public static function fromString(string $body, int $status = 200, array $headers = []): static
    {
        return new static(Stream::fromString($body), $status, $headers);
    }

    /**
     * from
     *
     * @param  ResponseInterface  $response
     *
     * @return  static
     *
     * @since  3.5.19
     */
    public static function from(ResponseInterface $response): ResponseInterface
    {
        return new static(
            $response->getBody(),
            $response->getStatusCode(),
            $response->getHeaders()
        );
    }

    /**
     * Constructor.
     *
     * @param  mixed  $body     The body data.
     * @param  int    $status   The status code.
     * @param  array  $headers  The custom headers.
     */
    public function __construct($body = 'php://memory', $status = 200, array $headers = [])
    {
        $stream = $body;

        if (!$stream instanceof StreamInterface) {
            $stream = new Stream($stream, Stream::MODE_READ_WRITE_RESET);
        }

        foreach ($headers as $name => $value) {
            $value = HeaderHelper::allToArray($value);

            if (!HeaderHelper::arrayOnlyContainsString($value)) {
                throw new \InvalidArgumentException('Header values should ony have string.');
            }

            if (!HeaderHelper::isValidName($name)) {
                throw new \InvalidArgumentException('Invalid header name');
            }

            $normalized                     = strtolower($name);
            $this->headerNames[$normalized] = $name;
            $this->headers[$name]           = $value;
        }

        $this->stream     = $stream;
        $this->statusCode = $status;
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @param  int     $code          The 3-digit integer result code to set.
     * @param  string  $reasonPhrase  The reason phrase to use with the
     *                                provided status code; if none is provided, implementations MAY
     *                                use the defaults as suggested in the HTTP specification.
     *
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = ''): Response|static
    {
        if (!ResponseHelper::validateStatus($code)) {
            throw new \InvalidArgumentException('Invalid status code: ' . $code);
        }

        $new               = clone $this;
        $new->statusCode   = (int) $code;
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Structure) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @return  string  Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase(): ?string
    {
        if (!$this->reasonPhrase) {
            $this->reasonPhrase = ResponseHelper::getPhrase($this->statusCode);
        }

        return $this->reasonPhrase;
    }
}
