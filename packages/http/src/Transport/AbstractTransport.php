<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Transport;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The AbstractTransport class.
 *
 * @since  2.1
 */
abstract class AbstractTransport implements TransportInterface
{
    use OptionAccessTrait;

    /**
     * Constructor.
     *
     * @param  array  $options  Client options object.
     *
     * @since   2.1
     */
    public function __construct(array $options = [])
    {
        $this->prepareOptions(
            [],
            $options
        );
    }

    /**
     * Send a request to the server and return a Response object with the response.
     *
     * @param  RequestInterface  $request  The request object to send.
     *
     * @param  array             $options
     *
     * @return  ResponseInterface
     *
     * @since   2.1
     */
    public function request(RequestInterface $request, array $options = []): ResponseInterface
    {
        $uri = $request->getUri()
            ->withPath('')
            ->withQuery('')
            ->withFragment('');

        $uri = $uri . $request->getRequestTarget();

        $request = $request->withRequestTarget($uri);

        return $this->doRequest($request, $options);
    }

    /**
     * Send a request to the server and return a Response object with the response.
     *
     * @param  RequestInterface  $request  The request object to store request params.
     * @param  array             $options
     *
     * @return  ResponseInterface
     *
     * @since   2.1
     */
    abstract protected function doRequest(RequestInterface $request, array $options = []): ResponseInterface;
}
