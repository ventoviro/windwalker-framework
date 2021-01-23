<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Output\OutputInterface;
use Windwalker\Http\Output\StreamOutput;

/**
 * The WebAdapter class.
 */
class PhpServer extends AbstractServer
{
    protected ?OutputInterface $output = null;

    protected HttpFactory $httpFactory;

    /**
     * WebAdapter constructor.
     *
     * @param  HttpFactory|null      $httpFactory
     * @param  OutputInterface|null  $output
     */
    public function __construct(?OutputInterface $output = null, ?HttpFactory $httpFactory = null)
    {
        $this->output      = $output ?? $this->getOutput();
        $this->httpFactory = $httpFactory ?? new HttpFactory();
    }

    public function listen(string $host = '0.0.0.0', int $port = 80, array $options = []): void
    {
        $this->handle(
            $options['request'] ?? $this->httpFactory->createServerRequestFromGlobals()
        );
    }

    public function handle(?ServerRequestInterface $request = null): void
    {
        /** @var RequestEvent $event */
        $event = $this->emit(
            RequestEvent::wrap('request')
                ->setRequest($request ?? $this->httpFactory->createServerRequestFromGlobals())
        );

        $this->getOutput()->respond(
            $event->getResponse() ?? $this->httpFactory->createResponse()
        );
    }

    public function stop(): void
    {
        //
    }

    /**
     * Method to get property Output
     *
     * @return  OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output ??= new StreamOutput();
    }

    /**
     * Method to set property output
     *
     * @param  OutputInterface  $output
     *
     * @return  static  Return self to support chaining.
     */
    public function setOutput(OutputInterface $output): static
    {
        $this->output = $output;

        return $this;
    }
}
