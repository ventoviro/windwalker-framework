<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Request;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Windwalker\Http\Helper\ServerHelper;
use Windwalker\Stream\PhpInputStream;

/**
 * Representation of an incoming, server-side HTTP request.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * Additionally, it encapsulates all data as it has arrived to the
 * application from the CGI and/or PHP environment, including:
 *
 * - The values represented in $_SERVER.
 * - Any cookies provided (generally via $_COOKIE)
 * - Query string arguments (generally via $_GET, or as parsed via parse_str())
 * - Upload files, if any (as represented by $_FILES)
 * - Deserialized body parameters (generally from $_POST)
 *
 * @since  2.1
 */
class ServerRequest extends AbstractRequest implements ServerRequestInterface
{
    /**
     * Property attributes.
     *
     * @var  array
     */
    protected array $attributes = [];

    /**
     * Property cookieParams.
     *
     * @var  array
     */
    protected array $cookieParams = [];

    /**
     * Property parsedBody.
     *
     * @var  array|object|null
     */
    protected array|object|null $parsedBody = null;

    /**
     * Property queryParams.
     *
     * @var  array
     */
    protected array $queryParams = [];

    /**
     * Property serverParams.
     *
     * @var  array
     */
    protected array $serverParams = [];

    /**
     * Property uploadedFiles.
     *
     * @var  UploadedFileInterface[]
     */
    protected array $uploadedFiles = [];

    /**
     * Class init
     *
     * @param  array                            $serverParams   Server parameters, typically from $_SERVER
     * @param  UploadedFileInterface[]          $uploadedFiles  Upload file information, a tree of UploadedFiles
     * @param  string                           $uri            URI for the request, if any.
     * @param  string                           $method         HTTP method for the request, if any.
     * @param  string|resource|StreamInterface  $body           Message body, if any.
     * @param  array                            $headers        Headers for the message, if any.
     * @param  array                            $cookies        Cookie values, typically is $_COOKIE.
     * @param  array                            $queryParams    Http query, typically is $_GET.
     * @param  array                            $parsedBody     Parsed body, typically is $_POST.
     * @param  string                           $protocol       The protocol version, default is 1.1.
     */
    public function __construct(
        array $serverParams = [],
        array $uploadedFiles = [],
        $uri = null,
        ?string $method = null,
        $body = 'php://input',
        array $headers = [],
        array $cookies = [],
        array $queryParams = [],
        array $parsedBody = null,
        string $protocol = '1.1'
    ) {
        if (!ServerHelper::validateUploadedFiles($uploadedFiles)) {
            throw new \InvalidArgumentException('Invalid uploaded files, every file should be an UploadedInterface');
        }

        if ($body === 'php://input') {
            $body = new PhpInputStream();
        }

        $this->serverParams  = $serverParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->cookieParams  = $cookies;
        $this->queryParams   = $queryParams;
        $this->parsedBody    = $parsedBody;
        $this->protocol      = $protocol;

        parent::__construct($uri, $method, $body, $headers);
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param  array  $cookies  Array of key/value pairs representing cookies.
     *
     * @return  static
     */
    public function withCookieParams(array $cookies): static|ServerRequest
    {
        $new = clone $this;

        $new->cookieParams = $cookies;

        return $new;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param  array  $query  Array of query string arguments, typically from
     *                        $_GET.
     *
     * @return static
     */
    public function withQueryParams(array $query): static|ServerRequest
    {
        $new = clone $this;

        $new->queryParams = $query;

        return $new;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return UploadedFileInterface[] An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param  array  $uploadedFiles  An array tree of UploadedFileInterface instances.
     *
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles): static|ServerRequest
    {
        if (!ServerHelper::validateUploadedFiles($uploadedFiles)) {
            throw new \InvalidArgumentException('Invalid uploaded files, every file should be an UploadedInterface');
        }

        $new = clone $this;

        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody(): array|object|null
    {
        return $this->parsedBody;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param  null|array|object  $data  The deserialized body data. This will
     *                                   typically be in an array or object.
     *
     * @return static
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function withParsedBody($data): static|ServerRequest
    {
        $new = clone $this;

        $new->parsedBody = $data;

        return $new;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @param  string  $name     The attribute name.
     * @param  mixed   $default  Default value to return if the attribute does not exist.
     *
     * @return mixed
     * @see getAttributes()
     *
     */
    public function getAttribute($name, $default = null): mixed
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$name];
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @param  string  $name   The attribute name.
     * @param  mixed   $value  The value of the attribute.
     *
     * @return static
     * @see getAttributes()
     *
     */
    public function withAttribute($name, $value): static|ServerRequest
    {
        $new = clone $this;

        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @param  string  $name  The attribute name.
     *
     * @return static
     * @see getAttributes()
     *
     */
    public function withoutAttribute($name): static|ServerRequest
    {
        if (!isset($this->attributes[$name])) {
            return clone $this;
        }

        $new = clone $this;

        unset($new->attributes[$name]);

        return $new;
    }
}
