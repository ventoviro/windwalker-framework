<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Uri;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Windwalker\Utilities\Assert\ArgumentsAssert;

/**
 * Uri Class
 *
 * Abstract base for out uri classes.
 *
 * This class should be considered an implementation detail. Typehint against UriInterface.
 *
 * @since  2.0
 */
class Uri implements UriInterface
{
    public const SCHEME = 1 << 0;

    public const USER = 1 << 1;

    public const PASSWORD = 1 << 2;

    public const HOST = 1 << 3;

    public const PORT = 1 << 4;

    public const PATH = 1 << 5;

    public const QUERY = 1 << 6;

    public const FRAGMENT = 1 << 7;

    public const USER_INFO = self::USER | self::PASSWORD;

    public const FULL_HOST = self::SCHEME | self::USER_INFO | self::HOST | self::PORT;

    public const URI = self::PATH | self::QUERY;

    public const ALL = (1 << 8) - 1;

    public const SCHEME_HTTP = 'http';

    public const SCHEME_HTTPS = 'https';

    protected ?string $original = null;

    protected ?string $scheme = null;

    protected ?string $host = null;

    protected ?int $port = null;

    protected ?string $user = null;

    protected ?string $pass = null;

    protected ?string $path = null;

    protected ?string $query = null;

    protected ?string $fragment = null;

    protected array $vars = [];

    protected array $standardSchemes = [
        'http' => 80,
        'https' => 443,
    ];

    /**
     * wrap
     *
     * @param  UriInterface|string|null  $uri
     *
     * @return  static
     */
    public static function wrap(UriInterface|string|null $uri): static
    {
        if ($uri instanceof static) {
            return $uri;
        }

        if ($uri instanceof UriInterface) {
            return new static((string) $uri);
        }

        return new static((string) $uri);
    }

    /**
     * Constructor.
     * You can pass a URI string to the constructor to initialise a specific URI.
     *
     * @param  string  $uri  The optional URI string
     *
     * @since   2.0
     */
    public function __construct(string $uri = '')
    {
        $this->parse($uri);
    }

    /**
     * Parse a given URI and populate the class fields.
     *
     * @param  string  $uri  The URI string to parse.
     *
     * @return  bool  True on success.
     *
     * @since   2.0
     */
    protected function parse(string $uri): bool
    {
        // Set the original URI to fall back on
        $this->original = $uri;

        /*
         * Parse the URI and populate the object fields. If URI is parsed properly,
         * set method return value to true.
         */

        $parts = UriHelper::parseUrl($uri);

        $retval = $parts ? true : false;

        // We need to replace &amp; with & for parse_str to work right...
        if (isset($parts['query']) && strpos($parts['query'], '&amp;')) {
            $parts['query'] = str_replace('&amp;', '&', $parts['query']);
        }

        $this->scheme   = $parts['scheme'] ?? null;
        $this->user     = $parts['user'] ?? null;
        $this->pass     = $parts['pass'] ?? null;
        $this->host     = $parts['host'] ?? null;
        $this->port     = $parts['port'] ?? null;
        $this->path     = $parts['path'] ?? null;
        $this->query    = $parts['query'] ?? null;
        $this->fragment = $parts['fragment'] ?? null;

        if ($this->path !== null) {
            $this->path = UriNormalizer::normalizePath($this->path);
        }

        // Parse the query
        if (isset($parts['query'])) {
            $this->vars = UriHelper::parseQuery($parts['query']);
        }

        return $retval;
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     *
     * @return  string  The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority(): string
    {
        if (empty($this->host)) {
            return '';
        }

        $authority = $this->host;

        $userInfo = $this->getUserInfo();

        if ($userInfo) {
            $authority = $userInfo . '@' . $authority;
        }

        if (!$this->isStandardPort($this->scheme, $this->host, $this->port)) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return  string  The URI user information, in "username[:password]" format.
     *
     * @since   2.1
     */
    public function getUserInfo(): string
    {
        $info = $this->user;

        if ($info && $this->pass) {
            $info .= ':' . $this->pass;
        }

        return (string) $info;
    }

    /**
     * Is a given port non-standard for the current scheme?
     *
     * @param  string|null  $scheme
     * @param  string|null  $host
     * @param  int|null     $port
     *
     * @return  bool
     */
    protected function isStandardPort(?string $scheme, ?string $host, ?int $port): bool
    {
        if (!$scheme) {
            return false;
        }

        if (!$host || !$port) {
            return true;
        }

        return (isset($this->standardSchemes[$scheme]) && $port === $this->standardSchemes[$scheme]);
    }

    /**
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param  string  $scheme  The scheme to use with the new instance.
     *
     * @return  static  A new instance with the specified scheme.
     *
     * @throws  InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme): Uri|static
    {
        if (!is_string($scheme)) {
            throw new InvalidArgumentException('URI Scheme should be a string.');
        }

        $scheme = UriHelper::filterScheme($scheme);

        $new         = clone $this;
        $new->scheme = $scheme;

        return $new;
    }

    /**
     * Return an instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param  string  $user      The user name to use for authority.
     * @param  string  $password  The password associated with $user.
     *
     * @return  static  A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null): Uri|static
    {
        ArgumentsAssert::assert(
            is_string($user),
            'URI User should be a string, %s given.'
        );

        ArgumentsAssert::assert(
            is_string($password) || $password === null,
            'URI Password should be a string or NULL, %s given.'
        );

        $new       = clone $this;
        $new->user = $user;
        $new->pass = $password;

        return $new;
    }

    /**
     * withUser
     *
     * @param  string|null  $user
     *
     * @return  static
     */
    public function withUser(?string $user): Uri|static
    {
        $new       = clone $this;
        $new->user = $user;

        return $new;
    }

    /**
     * withPassword
     *
     * @param  string|null  $password
     *
     * @return  static
     */
    public function withPassword(?string $password): Uri|static
    {
        $new       = clone $this;
        $new->pass = $password;

        return $new;
    }

    /**
     * Return an instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param  string  $host  The hostname to use with the new instance.
     *
     * @return  static  A new instance with the specified host.
     *
     * @throws  InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host): Uri|static
    {
        if (!is_string($host)) {
            throw new InvalidArgumentException('URI Host should be a string.');
        }

        $new       = clone $this;
        $new->host = $host;

        return $new;
    }

    /**
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param  int  $port   The port to use with the new instance; a null value
     *                      removes the port information.
     *
     * @return  static  A new instance with the specified port.
     * @throws  InvalidArgumentException for invalid ports.
     */
    public function withPort($port): Uri|static
    {
        if (is_array($port) || is_object($port)) {
            throw new InvalidArgumentException('Invalid port type.');
        }

        if ($port !== null) {
            $port = (int) $port;

            if ($port < 1 || $port > 65535) {
                throw new InvalidArgumentException(sprintf('Number of "%d" is not a valid TCP/UDP port', $port));
            }
        }

        $new       = clone $this;
        $new->port = $port;

        return $new;
    }

    /**
     * Return an instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * If the path is intended to be domain-relative rather than path relative then
     * it must begin with a slash ("/"). Paths not starting with a slash ("/")
     * are assumed to be relative to some base path known to the application or
     * consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param  string  $path  The path to use with the new instance.
     *
     * @return  static  A new instance with the specified path.
     * @throws  InvalidArgumentException for invalid paths.
     */
    public function withPath($path): Uri|static
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('URI Path should be a string.');
        }

        $path = (string) $path;

        if (str_contains($path, '?') || str_contains($path, '#')) {
            throw new InvalidArgumentException('Path should not contain `?` and `#` symbols.');
        }

        $path = UriNormalizer::normalizePath($path);
        $path = UriHelper::filterPath($path);

        $new       = clone $this;
        $new->path = $path;

        return $new;
    }

    /**
     * withQueryParams
     *
     * @param  array|string  $query
     *
     * @return  static
     *
     * @since  3.5.2
     */
    public function withQueryParams($query): Uri|static
    {
        if (!is_string($query)) {
            $query = UriHelper::buildQuery((array) $query);
        }

        return $this->withQuery($query);
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param  string  $query  The query string to use with the new instance.
     *
     * @return  static  A new instance with the specified query string.
     * @throws  InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query): Uri|static
    {
        if (!is_string($query)) {
            throw new InvalidArgumentException('URI Query should be a string.');
        }

        $query = UriHelper::filterQuery($query);

        $new        = clone $this;
        $new->vars  = UriHelper::parseQuery($query);
        $new->query = $query;

        return $new;
    }

    /**
     * withVar
     *
     * @param  string        $name
     * @param  array|string  $value
     *
     * @return  static
     *
     * @since  3.5.2
     */
    public function withVar(string $name, mixed $value): Uri|static
    {
        $new = clone $this;

        $query        = $new->vars;
        $query[$name] = $value;

        $query = UriHelper::filterQuery(UriHelper::buildQuery($query));

        $new->vars  = UriHelper::parseQuery($query);
        $new->query = $query;

        return $new;
    }

    /**
     * delVar
     *
     * @param  string  $name
     *
     * @return  static
     *
     * @since  3.5.2
     */
    public function withoutVar(string $name): Uri|static
    {
        $new = clone $this;

        unset($new->vars[$name]);

        $new->query = UriHelper::buildQuery($new->vars);

        return $new;
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param  string  $fragment  The fragment to use with the new instance.
     *
     * @return  static  A new instance with the specified fragment.
     */
    public function withFragment($fragment): Uri|static
    {
        if (!is_string($fragment)) {
            throw new InvalidArgumentException('URI Fragment should be a string.');
        }

        $fragment = UriHelper::filterFragment($fragment);

        $new           = clone $this;
        $new->fragment = $fragment;

        return $new;
    }

    /**
     * Magic method to get the string representation of the URI object.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Returns full uri string.
     *
     * @param  array  $parts  An array specifying the parts to render.
     *
     * @return  string  The rendered URI string.
     *
     * @since   2.0
     */
    public function toString(int $parts = self::ALL): string
    {
        // Make sure the query is created
        $query = $this->getQuery();

        $uri = '';
        $uri .= ($parts & static::SCHEME) ? (!empty($this->scheme) ? $this->scheme . '://' : '') : '';
        $uri .= ($parts & static::USER) ? $this->user : '';
        $uri .= ($parts & static::PASSWORD)
            ? (!empty($this->pass) ? ':' : '') . $this->pass . (!empty($this->user) ? '@' : '')
            : '';
        $uri .= ($parts & static::HOST) ? $this->host : '';
        $uri .= ($parts & static::PORT) ? (!empty($this->port) ? ':' : '') . $this->port : '';
        $uri .= ($parts & static::PATH) ? $this->path ? '/' . ltrim($this->path, '/') : '' : '';
        $uri .= ($parts & static::QUERY) ? (!empty($query) ? '?' . $query : '') : '';
        $uri .= ($parts & static::FRAGMENT) ? (!empty($this->fragment) ? '#' . $this->fragment : '') : '';

        return $uri;
    }

    /**
     * Returns flat query string.
     *
     * @return  string  Query string.
     *
     * @since   2.0
     */
    public function getQuery(): string
    {
        // If the query is empty build it first
        if ($this->query === null) {
            $this->query = UriHelper::buildQuery($this->vars);
        }

        return $this->query;
    }

    public function getQueryValues(): array
    {
        return $this->vars;
    }

    /**
     * Checks if variable exists.
     *
     * @param  string  $name  Name of the query variable to check.
     *
     * @return  bool  True if the variable exists.
     *
     * @since   2.0
     */
    public function hasVar(string $name): bool
    {
        return array_key_exists($name, $this->vars);
    }

    /**
     * Returns a query variable by name.
     *
     * @param  string  $name     Name of the query variable to get.
     * @param  string  $default  Default value to return if the variable is not set.
     *
     * @return  mixed   Query variables.
     *
     * @since   2.0
     */
    public function getVar(string $name, $default = null): mixed
    {
        return $this->vars[$name] ?? $default;
    }

    /**
     * Get URI username
     * Returns the username, or null if no username was specified.
     *
     * @return  string  The URI username.
     *
     * @since   2.0
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * Get URI password
     * Returns the password, or null if no password was specified.
     *
     * @return  string  The URI password.
     *
     * @since   2.0
     */
    public function getPassword(): string
    {
        return $this->pass;
    }

    /**
     * Get URI host
     * Returns the hostname/ip or null if no hostname/ip was specified.
     *
     * @return  string  The URI host.
     *
     * @since   2.0
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Get URI port
     * Returns the port number, or null if no port was specified.
     *
     * @return int|null The URI port number.
     *
     * @since   2.0
     */
    public function getPort(): ?int
    {
        return $this->port ?? null;
    }

    /**
     * Gets the URI path string.
     *
     * @return  string  The URI path string.
     *
     * @since   2.0
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Get the URI archor string
     * Everything after the "#".
     *
     * @return  string  The URI anchor string.
     *
     * @since   2.0
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * Checks whether the current URI is using HTTPS.
     *
     * @return  bool  True if using SSL via HTTPS.
     *
     * @since   2.0
     */
    public function isSSL(): bool
    {
        return $this->getScheme() === static::SCHEME_HTTPS;
    }

    /**
     * Get URI scheme (protocol)
     * ie. http, https, ftp, etc...
     *
     * @return  string  The URI scheme.
     *
     * @since   2.0
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * getUri
     *
     * @return  string
     */
    public function getOriginal(): string
    {
        return $this->original;
    }
}
