<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Stringable;

/**
 * Interface HttpClientInterface
 *
 * @since  2.1
 */
interface HttpClientInterface extends ClientInterface
{
    public const MULTIPART_FORMDATA = 'multipart/form-data';

    /**
     * Request a remote server.
     *
     * This method will build a Request object and use send() method to send request.
     *
     * @param  string             $method   The method type.
     * @param  string|Stringable  $url      The URL to request, may be string or Uri object.
     * @param  mixed              $body     The request body data, can be an array of POST data.
     * @param  array              $options  The options array.
     *
     * @return  ResponseInterface
     */
    public function request(
        string $method,
        Stringable|string $url,
        $body = null,
        array $options = []
    ): ResponseInterface;
}
