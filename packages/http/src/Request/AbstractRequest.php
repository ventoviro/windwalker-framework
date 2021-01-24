<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Request;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Http\MessageTrait;
use Windwalker\Http\Uri;
use Windwalker\Stream\Stream;

/**
 * The AbstractRequest class.
 */
abstract class AbstractRequest implements RequestInterface
{
    use MessageTrait;

    /**
     * Property method.
     *
     * @var string|null
     */
    protected ?string $method;

    /**
     * Property uri.
     *
     * @var UriInterface|null
     */
    protected ?UriInterface $uri;

    /**
     * Property requestTarget.
     *
     * @var  string
     */
    protected ?string $requestTarget = null;

    /**
     * Property allowMethods.
     *
     * @var  array
     */
    protected array $allowMethods = [
        'CONNECT',
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PUT',
        'TRACE',
    ];

    /**
     * Class init.
     *
     * @param  string|StreamInterface|UriInterface  $uri      The uri target of this request.
     * @param  string                               $method   The request method.
     * @param  string|StreamInterface               $body     The request body.
     * @param  array                                $headers  The request headers.
     */
    public function __construct(
        StreamInterface|UriInterface|string $uri = null,
        ?string $method = null,
        $body = 'php://memory',
        array $headers = []
    ) {
        if (!$body instanceof StreamInterface) {
            $body = new Stream($body, Stream::MODE_READ_WRITE_RESET);
        }

        if (!$uri instanceof UriInterface) {
            $uri = new Uri((string) $uri);
        }

        foreach ($headers as $name => $value) {
            $value = HeaderHelper::allToArray($value);

            if (!HeaderHelper::arrayOnlyContainsString($value)) {
                throw new InvalidArgumentException('Header values should ony have string.');
            }

            if (!HeaderHelper::isValidName($name)) {
                throw new InvalidArgumentException('Invalid header name');
            }

            $normalized                     = strtolower($name);
            $this->headerNames[$normalized] = $name;
            $this->headers[$name]           = $value;
        }

        $this->stream = $body;
        $this->method = $this->validateMethod($method);
        $this->uri    = $uri;
    }

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        if (!$this->uri) {
            return '/';
        }

        $target = $this->uri->getPath();

        if ($this->uri->getQuery()) {
            $target .= '?' . $this->uri->getQuery();
        }

        if (empty($target)) {
            $target = '/';
        }

        return $target;
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
     *     request-target forms allowed in request messages)
     *
     * @param  mixed  $requestTarget
     *
     * @return static
     * @throws InvalidArgumentException if the request target is invalid.
     */
    public function withRequestTarget(mixed $requestTarget): AbstractRequest|static
    {
        if (preg_match('/\s/', $requestTarget)) {
            throw new InvalidArgumentException('RequestTarget cannot contain whitespace.');
        }

        $new                = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param  string  $method  Case-sensitive method.
     *
     * @return static
     * @throws InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method): AbstractRequest|static
    {
        $method = $this->validateMethod($method);

        $new = clone $this;

        $new->method = $method;

        return $new;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri(): Uri|UriInterface|StreamInterface|string|null
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @param  UriInterface  $uri           New request URI to use.
     * @param  bool          $preserveHost  Preserve the original state of the Host header.
     *
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false): AbstractRequest|static
    {
        $new      = clone $this;
        $new->uri = $uri;

        if ($preserveHost) {
            return $new;
        }

        if (!$uri->getHost()) {
            return $new;
        }

        $host = $uri->getHost();

        if ($uri->getPort()) {
            $host .= ':' . $uri->getPort();
        }

        $new->headerNames['host'] = 'Host';
        $new->headers['Host']     = [$host];

        return $new;
    }

    /**
     * Validate method name.
     *
     * @param  string  $method  Method to validate.
     *
     * @return  string  Valid or not.
     */
    protected function validateMethod(?string $method): ?string
    {
        if ($method === null) {
            return null;
        }

        $method = strtoupper($method);

        if (!in_array($method, $this->allowMethods, true)) {
            throw new InvalidArgumentException('Invalid HTTP method: ' . $method);
        }

        return $method;
    }
}
