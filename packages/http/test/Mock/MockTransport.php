<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Mock;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Transport\AbstractTransport;

/**
 * The MockTransport class.
 *
 * @since  2.1
 */
class MockTransport extends AbstractTransport
{
    /**
     * Property request.
     *
     * @var  RequestInterface
     */
    public $request;

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
        $this->request = $request;

        return $this->doRequest($request);
    }

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
        return new Response();
    }

    /**
     * Method to check if HTTP transport layer available for using
     *
     * @return  bool  True if available else false
     *
     * @since   2.1
     */
    public static function isSupported(): bool
    {
        return true;
    }

    /**
     * Use stream to download file.
     *
     * @param  RequestInterface  $request  The request object to store request params.
     * @param  string|StreamInterface  $dest  The dest path to store file.
     *
     * @param  array  $options
     *
     * @return  ResponseInterface
     * @since   2.1
     */
    public function download(
        RequestInterface $request,
        string|StreamInterface $dest,
        array $options = []
    ): ResponseInterface {
        $this->setOption('target_file', $dest);

        return $this->request($request);
    }
}
