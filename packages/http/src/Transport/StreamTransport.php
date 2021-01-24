<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Transport;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use UnexpectedValueException;
use Windwalker\Http\Exception\HttpRequestException;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Http\Response\Response;
use Windwalker\Stream\Stream;
use Windwalker\Stream\StreamHelper;

/**
 * The StreamTransport class.
 *
 * @since  2.1
 */
class StreamTransport extends AbstractTransport
{
    /**
     * Send a request to the server and return a Response object with the response.
     *
     * @param  RequestInterface  $request  The request object to store request params.
     *
     * @param  array             $options
     *
     * @return  ResponseInterface
     *
     * @since   2.1
     */
    protected function doRequest(RequestInterface $request, array $options = []): ResponseInterface
    {
        $stream = $this->createStream($request, $options);

        if ($dest = ($options['target_file'] ?? null)) {
            $content = '';
            StreamHelper::copyTo($stream, $dest);
        } else {
            $content = $stream->getContents();
        }

        $metadata = $stream->getMetadata();

        $stream->close();

        if (isset($metadata['wrapper_data']['headers'])) {
            $headers = $metadata['wrapper_data']['headers'];
        } elseif (isset($metadata['wrapper_data'])) {
            $headers = $metadata['wrapper_data'];
        } else {
            $headers = [];
        }

        return $this->getResponse($headers, $content);
    }

    public function createConnection(RequestInterface $request, array $options = []): bool
    {
        // Create the stream context options array with the required method offset.
        $opt = ['method' => $request->getMethod()];

        // Set HTTP Version
        $opt['protocol_version'] = $request->getProtocolVersion();

        // If data exists let's encode it and make sure our Content-Type header is set.
        $data = (string) $request->getBody();

        if (isset($data)) {
            // If the data is a scalar value simply add it to the stream context options.
            if (is_scalar($data)) {
                $opt['content'] = $data;
            } else // Otherwise we need to encode the value first.
            {
                $opt['content'] = http_build_query($data);
            }

            if (!$request->getHeader('Content-Type')) {
                $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
            }

            // Add the relevant headers.
            $request = $request->withHeader('Content-Length', (string) strlen($opt['content']));
        }

        // Speed up stream get URL
        // @see http://stackoverflow.com/questions/3629504/php-file-get-contents-very-slow-when-using-full-url
        // @see http://stackoverflow.com/questions/13679976/how-to-speed-up-file-get-contents
        $request = $request->withHeader('Connection', 'Close');

        // Build the headers string for the request.
        if ($headers = $request->getHeaders()) {
            // Add the headers string into the stream context options array.
            $opt['header'] = HeaderHelper::toHeaderLines($headers, true);
        }

        // If an explicit timeout is given user it.
        if ($this->getOption('timeout')) {
            $opt['timeout'] = (int) $this->getOption('timeout');
        }

        // If an explicit user agent is given use it.
        if ($this->getOption('userAgent')) {
            $opt['user_agent'] = $this->getOption('userAgent');
        }

        // Ignore HTTP errors so that we can capture them.
        $opt['ignore_errors'] = 1;

        // Follow redirects.
        $opt['follow_location'] = (int) $this->getOption('follow_location', 1);

        $opt = array_merge($opt, $options);

        // Create the stream context for the request.
        $context = stream_context_create(['http' => $opt]);

        // Capture PHP errors
        return @fopen($request->getRequestTarget(), Stream::MODE_READ_ONLY_FROM_BEGIN, false, $context);
    }

    /**
     * createStream
     *
     * @param  RequestInterface  $request
     * @param  array             $options
     *
     * @return  Stream
     */
    protected function createStream(RequestInterface $request, array $options): Stream
    {
        $connection = $this->createConnection($request, $options);

        if (!$connection) {
            $error = error_get_last();

            throw new HttpRequestException($error['message'] ?? 'Unknown error');
        }

        return new Stream($connection);
    }

    /**
     * Method to get a response object from a server response.
     *
     * @param  array   $headers  The response headers as an array.
     * @param  string  $body     The response body as a string.
     *
     * @return  Response
     *
     * @throws  UnexpectedValueException
     * @since   2.1
     */
    protected function getResponse(array $headers, string $body): Response
    {
        // Create the response object.
        $return = new Response();

        // Set the body for the response.
        $return->getBody()->write($body);

        $return->getBody()->rewind();

        // Get the response code from the first offset of the response headers.
        preg_match('/[0-9]{3}/', array_shift($headers), $matches);
        $code = $matches[0];

        if (is_numeric($code)) {
            $return = $return->withStatus($code);
        } elseif (!$this->getOption('allow_empty_status_code', false)) {
            // No valid response code was detected.
            throw new UnexpectedValueException('No HTTP response code found.');
        }
        // Add the response headers to the response object.
        foreach ($headers as $header) {
            $pos = strpos($header, ':');

            $return = $return->withHeader(trim(substr($header, 0, $pos)), trim(substr($header, ($pos + 1))));
        }

        return $return;
    }

    /**
     * Use stream to download file.
     *
     * @param  RequestInterface        $request  The request object to store request params.
     * @param  string|StreamInterface  $dest     The dest path to store file.
     *
     * @param  array                   $options
     *
     * @return  ResponseInterface
     * @since   2.1
     */
    public function download(
        RequestInterface $request,
        string|StreamInterface $dest,
        array $options = []
    ): ResponseInterface {
        if (!$dest) {
            throw new InvalidArgumentException('Target file path is emptty.');
        }

        if (!$dest instanceof StreamInterface) {
            $dest = Stream::fromFilePath($dest);
        }

        $options['target_file'] = $dest;

        $response = $this->request($request, $options);

        $dest->close();

        return $response;
    }

    /**
     * Method to check if HTTP transport layer available for using
     *
     * @return  bool  True if available else false
     *
     * @since   2.0
     */
    public static function isSupported(): bool
    {
        return function_exists('fopen') && is_callable('fopen') && ini_get('allow_url_fopen');
    }
}
