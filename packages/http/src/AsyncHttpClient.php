<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RangeException;
use RuntimeException;
use Windwalker\Http\Promise\PromiseResponse as Promise;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Transport\CurlTransport;
use Windwalker\Http\Transport\TransportInterface;

/**
 * The AsyncRequest class.
 *
 * @method Promise get($url, $data = null, $headers = [])
 * @method Promise post($url, $data, $headers = [])
 * @method Promise put($url, $data, $headers = [])
 * @method Promise patch($url, $data, $headers = [])
 * @method Promise delete($url, $data = null, $headers = [])
 * @method Promise head($url, $headers = [])
 * @method Promise options($url, $headers = [])
 * @method Promise sendRequest(RequestInterface $request)
 * @method Promise request($method, $url, $body = null, $headers = [])
 *
 * @since  3.2
 */
class AsyncHttpClient extends HttpClient
{
    /**
     * Property mh.
     *
     * @var  resource
     */
    protected $mh;

    /**
     * Property handles.
     *
     * @var  []
     */
    protected $tasks = [];

    /**
     * Property errors.
     *
     * @var  RuntimeException[]
     */
    protected $errors = [];

    /**
     * Property stop.
     *
     * @var  bool
     */
    protected $stop = false;

    /**
     * Class init.
     *
     * @param  array          $options    The options of this client object.
     * @param  CurlTransport  $transport  The Transport handler, default is CurlTransport.
     */
    public function __construct($options = [], CurlTransport $transport = null)
    {
        parent::__construct($options, $transport);
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

            $this->errors = [];
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

        $this->mh    = null;
        $this->tasks = [];

        return $this;
    }

    /**
     * Send a request to remote.
     *
     * @param  RequestInterface  $request  The Psr Request object.
     *
     * @return  Promise
     * @throws RangeException
     */
    public function senRequest(RequestInterface $request): ResponseInterface
    {
        /** @var CurlTransport $transport */
        $transport = $this->getTransport();

        $this->tasks[] = [
            'handle' => $handle = $transport->createHandle($request),
            'promise' => $promise = new Promise(),
        ];

        curl_multi_add_handle($this->getMainHandle(), $handle);

        return $promise;
    }

    /**
     * resolve
     *
     * @param  callable  $callback
     *
     * @return  Response[]
     * @throws RuntimeException
     */
    public function resolve(callable $callback = null): array
    {
        $active = null;
        $mh     = $this->getMainHandle();

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
            throw new RuntimeException("Curl multi read error $mrc\n", E_USER_WARNING);
        }

        /** @var CurlTransport $transport */
        $responses = [];
        $errors    = [];
        $transport = $this->getTransport();

        foreach ($this->tasks as $task) {
            /** @var Promise $promise */
            $handle  = $task['handle'];
            $promise = $task['promise'];

            $error = curl_error($handle);

            if (!$error) {
                $responses[] = $res = $transport->getResponse(curl_multi_getcontent($handle), curl_getinfo($handle));
                $promise->resolve($res);
            } else {
                $errors[] = $e = new RuntimeException($error, curl_errno($handle));
                $promise->reject($e);
            }
        }

        $this->errors = $errors;

        if ($callback) {
            $callback($responses, $errors, $this);
        }

        $this->reset();

        return $responses;
    }

    /**
     * Method to set property transport
     *
     * @param  TransportInterface  $transport
     *
     * @return  static  Return self to support chaining.
     * @throws InvalidArgumentException
     */
    public function setTransport(TransportInterface $transport): static
    {
        if (!$transport instanceof CurlTransport) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s only supports %s',
                    get_called_class(),
                    CurlTransport::class
                )
            );
        }

        $this->transport = $transport;

        return $this;
    }

    /**
     * Method to get property Errors
     *
     * @return  RuntimeException[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Method to get property Handles
     *
     * @return  resource[]
     */
    public function getHandles(): array
    {
        return array_column($this->tasks, 'handle');
    }

    /**
     * Method to get property Tasks
     *
     * @return  resource[]
     *
     * @since  3.4
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }
}
