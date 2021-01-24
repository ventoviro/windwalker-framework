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

use function Windwalker\Promise\async;

/**
 * The SimpleAsyncTransport class.
 */
class SimpleAsyncTransport implements AsyncTransportInterface
{
    protected TransportInterface $transport;

    /**
     * SimpleAsyncTransport constructor.
     *
     * @param  TransportInterface  $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request, array $options = [])
    {
        return async(fn() => $this->getTransport()->request($request, $options));
    }

    /**
     * @return TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    /**
     * @param  TransportInterface  $transport
     *
     * @return  static  Return self to support chaining.
     */
    public function setTransport(TransportInterface $transport): static
    {
        $this->transport = $transport;

        return $this;
    }
}
