<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The Server class.
 *
 * @since  3.0
 */
class HttpServer extends AdaptableServer
{
    use OptionAccessTrait;

    protected \Closure $handler;

    /**
     * Create a Server instance.
     *
     * @param  array                 $options
     * @param  ServerInterface|null  $adapter
     * @param  callable|null         $handler
     */
    public function __construct(array $options = [], ?ServerInterface $adapter = null, callable $handler = null) {
        $this->prepareOptions(
            [],
            $options
        );

        $this->handler = \Closure::fromCallable(
            $handler ?? fn ($server, $host, $port, $options) => $this->getAdapter()->listen($host, $port, $options)
        );

        parent::__construct($adapter);

        $this->adapter->getDispatcher()
            ->registerDealer($this->getDispatcher());
    }

    /**
     * Execute the server.
     *
     * @param  string  $host
     * @param  int     $port
     * @param  array   $options
     */
    public function listen(string $host = '0.0.0.0', int $port = 0, array $options = []): void
    {
        $options = Arr::mergeRecursive($this->options, $options);

        ($this->handler)($this->getAdapter(), $host, $port, $options);
    }

    public function stop(): void
    {
        $this->adapter->stop();
    }

    /**
     * @return \Closure
     */
    public function getHandler(): \Closure
    {
        return $this->handler;
    }

    /**
     * @param  \Closure  $handler
     *
     * @return  static  Return self to support chaining.
     */
    public function setHandler(\Closure $handler): static
    {
        $this->handler = $handler;

        return $this;
    }
}
