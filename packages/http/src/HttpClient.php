<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RangeException;
use ReflectionMethod;
use Stringable;
use Windwalker\Http\Request\Request;
use Windwalker\Http\Stream\RequestBodyStream;
use Windwalker\Http\Transport\AsyncTransportInterface;
use Windwalker\Http\Transport\CurlTransport;
use Windwalker\Http\Transport\MultiCurlTransport;
use Windwalker\Http\Transport\TransportInterface;
use Windwalker\Promise\PromiseInterface;
use Windwalker\Stream\Stream;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Exception\ExceptionFactory;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\TypeCast;

/**
 * The HttpClient class.
 *
 * @method PromiseInterface|mixed optionsAsync(Stringable|string $url, array $options = [])
 * @method PromiseInterface|mixed headAsync(Stringable|string $url, array $options = [])
 * @method PromiseInterface|mixed getAsync(Stringable|string $url, array $options = [])
 * @method PromiseInterface|mixed postAsync(Stringable|string $url, mixed $body, array $options = [])
 * @method PromiseInterface|mixed putAsync(Stringable|string $url, mixed $body, array $options = [])
 * @method PromiseInterface|mixed deleteAsync(Stringable|string $url, mixed $body, array $options = [])
 * @method PromiseInterface|mixed patchAsync(Stringable|string $url, mixed $body, array $options = [])
 * @method PromiseInterface|mixed traceAsync(Stringable|string $url, array $options = [])
 *
 * @since  2.1
 */
class HttpClient implements HttpClientInterface, AsyncHttpClientInterface
{
    use OptionAccessTrait;

    /**
     * Property transport.
     *
     * @var  TransportInterface
     */
    protected TransportInterface $transport;

    protected ?AsyncTransportInterface $asyncTransport = null;

    /**
     * create
     *
     * @param  array                    $options
     * @param  TransportInterface|null  $transport
     *
     * @return  static
     *
     * @since  3.5.19
     */
    public static function create(
        array $options = [],
        ?TransportInterface $transport = null
    ): static {
        return new static($options, $transport);
    }

    /**
     * Class init.
     *
     * @param  array                    $options    The options of this client object.
     * @param  TransportInterface|null  $transport  The Transport handler, default is CurlTransport.
     */
    public function __construct($options = [], ?TransportInterface $transport = null)
    {
        $this->prepareOptions(
            [],
            $options
        );
        $this->transport = $transport ?? new CurlTransport();
    }

    /**
     * Download file to target path.
     *
     * @param  Stringable|string    $url      The URL to request, may be string or Uri object.
     * @param  string|              $dest     The dest file path can be a StreamInterface.
     * @param  mixed                $body     The request body data, can be an array of POST data.
     * @param  array                $options  The options array.
     *
     * @return  ResponseInterface
     */
    public function download(
        Stringable|string $url,
        string $dest,
        $body = null,
        array $options = []
    ): ResponseInterface {
        $request = $this->preprocessRequest(new Request(), 'GET', $url, $body, $options);

        $transport = $this->getTransport();

        if (!$transport::isSupported()) {
            throw new RangeException(get_class($transport) . ' driver not supported.');
        }

        return $transport->download($request, $dest);
    }

    /**
     * Send a request to remote.
     *
     * @param  RequestInterface  $request  The Psr Request object.
     *
     * @return  ResponseInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $transport = $this->getTransport();

        if (!$transport::isSupported()) {
            throw new RangeException(get_class($transport) . ' driver not supported.');
        }

        return $transport->request($request);
    }

    /**
     * Method to send the OPTIONS command to the server.
     *
     * @param  Stringable|string  $url      Path to the resource.
     * @param  array              $options  An array of name-value pairs to include in the header of the request.
     *
     * @return  ResponseInterface
     *
     * @since   2.1
     */
    public function options(Stringable|string $url, array $options = []): ResponseInterface
    {
        return $this->request('OPTIONS', $url, null, $options);
    }

    /**
     * Method to send the HEAD command to the server.
     *
     * @param  Stringable|string  $url      Path to the resource.
     * @param  array              $options  An array of name-value pairs to include in the header of the request.
     *
     * @return  ResponseInterface
     *
     * @since   2.1
     */
    public function head(Stringable|string $url, array $options = []): ResponseInterface
    {
        return $this->request('HEAD', $url, null, $options);
    }

    /**
     * Method to send the GET command to the server.
     *
     * @param  Stringable|string  $url      Path to the resource.
     * @param  array              $options  An array of name-value pairs to include in the header of the request.
     *
     * @return  ResponseInterface
     *
     * @since   2.1
     */
    public function get(Stringable|string $url, $options = []): ResponseInterface
    {
        return $this->request('GET', $url, null, $options);
    }

    /**
     * Method to send the POST command to the server.
     *
     * @param  Stringable|string  $url      Path to the resource.
     * @param  mixed              $body     Either an associative array or a string to be sent with the request.
     * @param  array              $options  An array of name-value pairs to include in the header of the request
     *
     * @return  ResponseInterface
     *
     * @since   2.1
     */
    public function post(Stringable|string $url, mixed $body, array $options = []): ResponseInterface
    {
        return $this->request('POST', $url, $body, $options);
    }

    /**
     * Method to send the PUT command to the server.
     *
     * @param  Stringable|string  $url      Path to the resource.
     * @param  mixed              $body     Either an associative array or a string to be sent with the request.
     * @param  array              $options  An array of name-value pairs to include in the header of the request.
     *
     * @return  ResponseInterface
     *
     * @since   2.1
     */
    public function put(Stringable|string $url, mixed $body, array $options = []): ResponseInterface
    {
        return $this->request('PUT', $url, $body, $options);
    }

    /**
     * Method to send the DELETE command to the server.
     *
     * @param  Stringable|string  $url      Path to the resource.
     * @param  mixed              $body     Either an associative array or a string to be sent with the request.
     * @param  array              $options  An array of name-value pairs to include in the header of the request.
     *
     * @return  ResponseInterface
     *
     * @since   2.1
     */
    public function delete(Stringable|string $url, mixed $body = null, array $options = []): ResponseInterface
    {
        return $this->request('DELETE', $url, $body, $options);
    }

    /**
     * Method to send the TRACE command to the server.
     *
     * @param  Stringable|string  $url      Path to the resource.
     * @param  array              $options  An array of name-value pairs to include in the header of the request.
     *
     * @return  ResponseInterface
     *
     * @since   2.1
     */
    public function trace(Stringable|string $url, array $options = []): ResponseInterface
    {
        return $this->request('TRACE', $url, null, $options);
    }

    /**
     * Method to send the PATCH command to the server.
     *
     * @param  Stringable|string  $url      Path to the resource.
     * @param  mixed              $body     Either an associative array or a string to be sent with the request.
     * @param  array              $options  An array of name-value pairs to include in the header of the request.
     *
     * @return  ResponseInterface
     *
     * @since   2.1
     */
    public function patch(Stringable|string $url, mixed $body, array $options = []): ResponseInterface
    {
        return $this->request('PATCH', $url, $body, $options);
    }

    /**
     * Method to get property Transport
     *
     * @return  TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    /**
     * Method to set property transport
     *
     * @param  TransportInterface  $transport
     *
     * @return  static  Return self to support chaining.
     */
    public function setTransport(TransportInterface $transport): static
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * Create Request object.
     *
     * @param  string             $method   The method type.
     * @param  Stringable|string  $url      The URL to request, may be string or Uri object.
     * @param  mixed              $body     The request body data, can be an array of POST data.
     * @param  array              $options  The options array.
     *
     * @return  RequestInterface
     *
     * @since  3.5.6
     */
    public static function createRequest(
        string $method,
        Stringable|string $url,
        $body = '',
        array $options = []
    ): RequestInterface {
        return static::prepareRequest(new Request(), $method, $url, $body, $options);
    }

    /**
     * Prepare Request object.
     *
     * @param  RequestInterface   $request  The Psr Request object.
     * @param  string             $method   The method type.
     * @param  Stringable|string  $url      The URL to request, may be string or Uri object.
     * @param  mixed              $body     The request body data, can be an array of POST data.
     * @param  array              $options  The options array.
     *
     * @return  RequestInterface
     *
     * @since  3.5.6
     */
    public static function prepareRequest(
        RequestInterface $request,
        string $method,
        Stringable|string $url,
        $body = '',
        array $options = []
    ): RequestInterface {
        // If is GET, we merge data into URL.
        if ($options['params'] ?? []) {
            $uri = Uri::wrap((string) $url);

            foreach ($options['params'] as $k => $v) {
                $uri = $uri->withVar($k, $v);
            }

            $url  = (string) $uri;
            $body = null;
        }

        $url = (string) $url;

        $request = $request->withRequestTarget($url)
            ->withMethod($method);

        // Override with this method
        foreach ($options['headers'] ?? [] as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        // If not GET, convert data to query string.
        if (is_string($body)) {
            $body = Stream::fromString($body);
        } else {
            $body = new RequestBodyStream(TypeCast::toArray($body));
        }

        return $request->withBody($body);
    }

    /**
     * Prepare Request object to send request.
     *
     * @param  RequestInterface   $request  The Psr Request object.
     * @param  string             $method   The method type.
     * @param  string|Stringable  $url      The URL to request, may be string or Uri object.
     * @param  mixed              $body     The request body data, can be an array of POST data.
     * @param  array              $options  The options array.
     *
     * @return  RequestInterface
     */
    protected function preprocessRequest(
        RequestInterface $request,
        string $method,
        Stringable|string $url,
        mixed $body,
        array $options
    ): RequestInterface {
        $options = Arr::mergeRecursive($this->getOptions(), $options);

        return static::prepareRequest($request, $method, $url, $body, $options);
    }

    /**
     * Request a remote server.
     *
     * This method will build a Request object and use send() method to send request.
     *
     * @param  string             $method   The method type.
     * @param  Stringable|string  $url      The URL to request, may be string or Uri object.
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
    ): ResponseInterface {
        $request = $this->preprocessRequest(new Request(), $method, $url, $body, $options);

        return $this->sendRequest($request);
    }

    /**
     * Request a remote server.
     *
     * This method will build a Request object and use send() method to send request.
     *
     * @param  string             $method   The method type.
     * @param  Stringable|string  $url      The URL to request, may be string or Uri object.
     * @param  mixed              $body     The request body data, can be an array of POST data.
     * @param  array              $options  The options array.
     *
     * @return PromiseInterface|mixed
     */
    public function requestAsync(
        string $method,
        Stringable|string $url,
        $body = null,
        array $options = []
    ): mixed {
        $request = $this->preprocessRequest(new Request(), $method, $url, $body, $options);

        return $this->sendAsyncRequest($request);
    }

    public function __call(string $name, array $args)
    {
        if (str_ends_with(strtolower($name), 'async')) {
            $method = substr($name, 0, -5);

            if (method_exists($this, $method)) {
                $ref = new ReflectionMethod($this, $method);

                if (count($ref->getParameters()) >= 3) {
                    $promise = $this->requestAsync($method, ...$args);
                } else {
                    $promise = $this->requestAsync($method, $args[0] ?? '', null, $args[1] ?? []);
                }

                return $promise;
            }
        }

        throw ExceptionFactory::badMethodCall($name);
    }

    /**
     * sendAsyncRequest
     *
     * @param  RequestInterface  $request
     *
     * @return  PromiseInterface|mixed
     */
    public function sendAsyncRequest(RequestInterface $request): mixed
    {
        return $this->getAsyncTransport()->sendRequest($request);
    }

    /**
     * @return AsyncTransportInterface
     */
    public function getAsyncTransport(): AsyncTransportInterface
    {
        return $this->asyncTransport
            ??= new MultiCurlTransport([], $this->getTransport());
    }

    /**
     * @param  AsyncTransportInterface|null  $asyncTransport
     *
     * @return  static  Return self to support chaining.
     */
    public function setAsyncTransport(?AsyncTransportInterface $asyncTransport): static
    {
        $this->asyncTransport = $asyncTransport;

        return $this;
    }
}
