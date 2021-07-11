<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Output;

use Psr\Http\Message\ResponseInterface;

/**
 * The StreamOutput class.
 *
 * @since  3.0
 */
class StreamOutput extends Output
{
    protected int $maxBufferLength = 8192;

    /**
     * Delay every loop for microseconds.
     *
     * @var int|null
     */
    protected ?int $delay = null;

    /**
     * Method to send the application response to the client.  All headers will be sent prior to the main
     * application output data.
     *
     * @param  ResponseInterface  $response  Respond body output.
     *
     * @return  void
     *
     * @since   3.0
     */
    public function respond(ResponseInterface $response): void
    {
        // $response = static::prepareContentLength($response);

        parent::respond($response);
    }

    /**
     * Send body as response.
     *
     * @param  ResponseInterface  $response  Response object.
     *
     * @return  void
     */
    public function sendBody(ResponseInterface $response): void
    {
        $range = $this->getContentRange($response->getHeaderLine('content-range'));

        $maxBufferLength = $this->getMaxBufferLength() ?: 8192;

        $fp = fopen('php://output', 'wb+');

        if ($range === false) {
            $body = $response->getBody();

            if ($body->isSeekable()) {
                $body->rewind();
            }

            while (!$body->eof()) {
                fwrite($fp, $body->read($maxBufferLength));

                $this->delay();
            }

            return;
        }

        [$unit, $first, $last, $length] = array_values($range);

        ++$last;

        $body = $response->getBody();
        $body->seek($first);
        $position = $first;

        while (!$body->eof() && $position < $last) {
            // The latest part
            if (($position + $maxBufferLength) > $last) {
                fwrite($fp, $body->read($last - $position));

                $this->delay();

                break;
            }

            fwrite($fp, $body->read($maxBufferLength));

            $position = $body->tell();

            $this->delay();
        }

        fclose($fp);
    }

    /**
     * Prepare content-length header.
     *
     * @param  ResponseInterface  $response  The response object with headers.
     *
     * @return  ResponseInterface
     */
    public static function prepareContentLength(ResponseInterface $response): ResponseInterface
    {
        if (!$response->hasHeader('content-length')) {
            if ($response->getBody()->getSize() !== null) {
                return $response->withHeader('content-length', (string) $response->getBody()->getSize());
            }
        }

        return $response;
    }

    /**
     * Parse content-range header to an array.
     *
     * @see  http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.16
     *
     * @param  string  $header
     *
     * @return  false|array  An array with [unit, first, last, length] elements;
     */
    protected function getContentRange(string $header): bool|array
    {
        if (preg_match('/(?P<unit>[\w]+)\s+(?P<first>\d+)-(?P<last>\d+)\/(?P<length>\d+|\*)/', $header, $matches)) {
            $return = [];

            $return['unit'] = $matches['unit'];
            $return['first'] = (int) $matches['first'];
            $return['last'] = (int) $matches['last'];
            $return['length'] = is_numeric($matches['length']) ? (int) $matches['length'] : '*';

            return $return;
        }

        return false;
    }

    /**
     * Method to get property MaxBufferLength
     *
     * @return  int
     */
    public function getMaxBufferLength(): int
    {
        return $this->maxBufferLength;
    }

    /**
     * Method to set property maxBufferLength
     *
     * @param  int  $maxBufferLength
     *
     * @return  static  Return self to support chaining.
     */
    public function setMaxBufferLength(int $maxBufferLength): static
    {
        $this->maxBufferLength = $maxBufferLength;

        return $this;
    }

    /**
     * Method to get property Delay
     *
     * @return  int
     */
    public function getDelay(): ?int
    {
        return $this->delay;
    }

    /**
     * Method to set property delay
     *
     * @param  int  $delay
     *
     * @return  static  Return self to support chaining.
     */
    public function setDelay(int $delay): static
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Delay the output loop.
     *
     * @return  void
     */
    protected function delay(): void
    {
        if ($this->delay === null) {
            return;
        }

        usleep($this->delay);
    }
}
