<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Request;

/**
 * The Request class.
 *
 * @since  2.1
 */
class Request extends AbstractRequest
{
    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return array Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders(): array
    {
        $headers = $this->headers;

        if (!$this->hasHeader('host') && ($this->uri && $this->uri->getHost())) {
            $headers['Host'] = [$this->getHostFromUri()];
        }

        return $headers;
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param  string  $name  Case-insensitive header field name.
     *
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name): array
    {
        if (!$this->hasHeader($name)) {
            if (strtolower($name) === 'host' && ($this->uri && $this->uri->getHost())) {
                return [$this->getHostFromUri()];
            }

            return [];
        }

        $name = $this->getHeaderName($name);

        return (array) $this->headers[$name];
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param  string  $name  Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name): bool
    {
        if (strtolower($name) === 'host' && ($this->uri && $this->uri->getHost())) {
            $this->headerNames['host'] = $name;
            $this->headers[$name] = [$this->getHostFromUri()];
        }

        return parent::hasHeader($name);
    }

    /**
     * Retrieve the host from the URI instance
     *
     * @return string
     */
    protected function getHostFromUri(): string
    {
        $host = $this->uri->getHost();
        $host .= $this->uri->getPort() ? ':' . $this->uri->getPort() : '';

        return $host;
    }
}
