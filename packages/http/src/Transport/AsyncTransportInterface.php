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
use Windwalker\Promise\PromiseInterface;

/**
 * Interface AsyncTransportInterface
 */
interface AsyncTransportInterface
{
    /**
     * sendRequest
     *
     * @param  RequestInterface  $request
     *
     * @param  array             $options
     *
     * @return  mixed|PromiseInterface
     */
    public function sendRequest(RequestInterface $request, array $options = []): mixed;
}
