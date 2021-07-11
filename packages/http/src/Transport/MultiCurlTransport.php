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
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Windwalker\Http\Exception\HttpRequestException;
use Windwalker\Promise\Promise;
use Windwalker\Promise\PromiseInterface;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The MultiCurlHandler class.
 */
class MultiCurlTransport implements AsyncTransportInterface
{
    use OptionAccessTrait;

    /**
     * @var resource
     */
    protected $mh;

    protected ?PromiseInterface $promise = null;

    /**
     * Property handles.
     *
     * @var  array[]
     */
    protected array $tasks = [];

    /**
     * @var TransportInterface|null
     */
    protected ?TransportInterface $transport;

    /**
     * Class init.
     *
     * @param  array               $options    The options of this client object.
     * @param  CurlTransport|null  $transport  The Transport handler, default is CurlTransport.
     */
    public function __construct($options = [], CurlTransport $transport = null)
    {
        $this->prepareOptions([], $options);

        $this->transport = $transport ?? new CurlTransport();
    }

    /**
     * getHandle
     *
     * @return  resource
     */
    public function getMainHandle()
    {
        if (!$this->mh) {
            $this->mh = curl_multi_init();
        }

        return $this->mh;
    }

    /**
     * reset
     *
     * @return  static
     */
    public function reset(): static
    {
        foreach ($this->tasks as $task) {
            curl_multi_remove_handle($this->mh, $task['handle']);
        }

        curl_multi_close($this->mh);

        $this->mh = null;
        $this->tasks = [];
        $this->promise = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request, array $options = [])
    {
        /** @var CurlTransport $transport */
        $transport = $this->getTransport();

        $this->tasks[] = [
            'handle' => $handle = $transport->createHandle($request, $options),
            'promise' => $promise = new Promise(),
        ];

        curl_multi_add_handle($this->getMainHandle(), $handle);

        return $this->prepareResolvePromise()->then(fn() => $promise);
    }

    /**
     * resolve
     *
     * @return  mixed|PromiseInterface
     */
    public function resolve(): mixed
    {
        $this->promise ??= $this->prepareResolvePromise();

        return $this->promise->wait();
    }

    protected function prepareResolvePromise(): PromiseInterface
    {
        return $this->promise ??= new Promise(
            function (callable $resolve) {
                if ($this->tasks === []) {
                    $resolve();

                    return;
                }

                $active = null;
                $mh = $this->getMainHandle();
                $promises = [];

                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc === CURLM_CALL_MULTI_PERFORM);

                while ($active && $mrc === CURLM_OK) {
                    if (curl_multi_select($mh) === -1) {
                        usleep(100);
                    }

                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc === CURLM_CALL_MULTI_PERFORM);
                }

                if ($mrc !== CURLM_OK) {
                    throw new RuntimeException(
                        "Curl multi read error $mrc\n",
                        E_USER_WARNING
                    );
                }

                /** @var CurlTransport $transport */
                $transport = $this->getTransport();

                foreach ($this->tasks as $task) {
                    /** @var Promise $promise */
                    $handle = $task['handle'];
                    $promise = $task['promise'];
                    $promises[] = $promise;

                    $error = curl_error($handle);

                    if (!$error) {
                        $res = $transport->getResponse(curl_multi_getcontent($handle), curl_getinfo($handle));
                        $promise->resolve($res);
                    } else {
                        $promise->reject(new HttpRequestException($error, curl_errno($handle)));
                    }
                }

                $this->reset();

                $resolve(Promise::all($promises));
            }
        );
    }

    /**
     * download
     *
     * @param  RequestInterface        $request
     * @param  StreamInterface|string  $dest
     * @param  array                   $options
     *
     * @return  mixed
     */
    public function download(RequestInterface $request, StreamInterface|string $dest, array $options = []): mixed
    {
    }

    /**
     * isSupported
     *
     * @return  mixed
     */
    public static function isSupported(): mixed
    {
    }

    /**
     * @return CurlTransport
     */
    public function getTransport(): CurlTransport
    {
        return $this->transport;
    }

    /**
     * @param  CurlTransport  $transport
     *
     * @return  static  Return self to support chaining.
     */
    public function setTransport(CurlTransport $transport): static
    {
        $this->transport = $transport;

        return $this;
    }
}
