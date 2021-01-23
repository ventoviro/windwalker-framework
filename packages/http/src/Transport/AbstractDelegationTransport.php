<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Transport;

/**
 * The DelegationTransport class.
 */
abstract class AbstractDelegationTransport
{
    /**
     * @var TransportInterface|null
     */
    protected ?TransportInterface $transport;

    /**
     * AbstractDelegationTransport constructor.
     *
     * @param  array                    $options
     * @param  TransportInterface|null  $transport
     */
    public function __construct(array $options = [], ?TransportInterface $transport = null)
    {
        $this->transport = $transport;
    }

    /**
     * @return TransportInterface|null
     */
    public function getTransport(): ?TransportInterface
    {
        return $this->transport;
    }

    /**
     * @param  TransportInterface|null  $transport
     *
     * @return  static  Return self to support chaining.
     */
    public function setTransport(?TransportInterface $transport): static
    {
        $this->transport = $transport;

        return $this;
    }
}
