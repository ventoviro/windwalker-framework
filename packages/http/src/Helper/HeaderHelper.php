<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Helper;

use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\ResponseInterface;

/**
 * The HeaderHelper class.
 *
 * @since  2.1
 *
 * todo: Support php8 types hint
 */
abstract class HeaderHelper
{
    /**
     * Get header value.
     *
     * The key will be lower case to search header value and implode array to string by comma.
     *
     * @param  array  $headers The headers wqe want to search.
     * @param  string $name    The name to search.
     * @param  mixed  $default The default value if not found.
     *
     * @return string  Found header value.
     *
     * @since  3.0
     */
    #[Pure]
    public static function getValue(array $headers, string $name, mixed $default = null): mixed
    {
        $name = strtolower($name);
        $headers = array_change_key_case($headers, CASE_LOWER);

        if (array_key_exists($name, $headers)) {
            return is_array($headers[$name]) ? implode(', ', $headers[$name]) : $headers[$name];
        }

        return $default;
    }

    /**
     * Check whether or not a header name is valid.
     *
     * This method based on phly/http
     *
     * @param   mixed $name The header to validate.
     *
     * @return  boolean  Valid or not.
     *
     * @see http://tools.ietf.org/html/rfc7230#section-3.2
     */
    public static function isValidName(mixed $name): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', (string) $name);
    }

    /**
     * Method to remove invalid CRLF injection from header value.
     *
     * Follows RFC-7230, only allows visible ASCII characters, spaces
     * and tabs in header value. every new line must only contains
     * a single CRLF and a space or tab after it.
     *
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     * @see https://tools.ietf.org/html/rfc7230
     *
     * @param   string $value The value to filter.
     *
     * @return  string  Filtered value.
     */
    public static function filter(mixed $value): string
    {
        $value = (string) $value;
        $length = strlen($value);
        $string = '';

        for ($i = 0; $i < $length; ++$i) {
            $ascii = ord($value[$i]);

            // Detect continuation sequences
            if ($ascii === 13) {
                $lf = ord($value[$i + 1]);
                $ws = ord($value[$i + 2]);

                if ($lf === 10 && in_array($ws, [9, 32], true)) {
                    $string .= $value[$i] . $value[$i + 1];
                    ++$i;
                }

                continue;
            }

            // Non-visible, non-whitespace characters
            // 9 === horizontal tab
            // 32-126, 128-254 === visible
            // 127 === DEL
            // 255 === null byte
            if (($ascii < 32 && $ascii !== 9) || $ascii === 127 || $ascii > 254) {
                continue;
            }

            $string .= $value[$i];
        }

        return $string;
    }

    /**
     * Method to validate a header value.
     *
     * Follows RFC-7230, only allows visible ASCII characters, spaces
     * and tabs in header value. every new line must only contains
     * a single CRLF and a space or tab after it.
     *
     * @param  mixed  $value
     *
     * @return  boolean  Valid or not.
     *
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     * @see https://tools.ietf.org/html/rfc7230
     */
    public static function isValidValue(mixed $value): bool
    {
        $value = (string) $value;

        // Look for:
        // \n not preceded by \r, OR
        // \r not followed by \n, OR
        // \r\n not followed by space or horizontal tab; these are all CRLF attacks
        if (preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $value)) {
            return false;
        }

        $length = strlen($value);

        for ($i = 0; $i < $length; $i += 1) {
            $ascii = ord($value[$i]);

            // Non-visible, non-whitespace characters
            // 9 === horizontal tab
            // 10 === line feed
            // 13 === carriage return
            // 32-126, 128-254 === visible
            // 127 === DEL
            // 255 === null byte
            if (($ascii < 32 && !in_array($ascii, [9, 10, 13], true)) || $ascii === 127 || $ascii > 254) {
                return false;
            }
        }

        return true;
    }

    /**
     * Method to validate protocol version format.
     *
     * Only allow 1.0, 1.1 and 2.
     *
     * @param   string $version Version string to validate.
     *
     * @return  boolean  Valid or not.
     */
    public static function isValidProtocolVersion(mixed $version): bool
    {
        if (!is_string($version) || empty($version)) {
            return false;
        }

        return (bool) preg_match('#^(1\.[01]|2)$#', $version);
    }

    /**
     * Convert values to array.
     *
     * @param   mixed $value Value to convert to array.
     *
     * @return  array  Converted array.
     */
    public static function allToArray($value): array
    {
        if ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }

        if (is_object($value)) {
            $value = get_object_vars($value);
        }

        $value = (array) $value;

        foreach ($value as $k => $v) {
            if (!static::isValidValue($v)) {
                throw new \InvalidArgumentException('Value :' . print_r($value, true) . ' is invalid.');
            }
        }

        return $value;
    }

    /**
     * Validate is an array only contains string.
     *
     * @param   array $array An array to validate.
     *
     * @return  boolean  valid or not.
     */
    public static function arrayOnlyContainsString(array $array): bool
    {
        foreach ($array as $value) {
            if (!is_string($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convert every header values to one line and merge multiple values with comma.
     *
     * @param array $headers  Headers to convert,
     * @param bool  $toString If true, will implode all header lines with line break.
     *
     * @return  array|string  Converted headers.
     *
     *
     */
    public static function toHeaderLines($headers, $toString = false): array|string
    {
        $headerArray = [];

        foreach ($headers as $key => $value) {
            $value = is_array($value) ? implode(',', $value) : $value;

            $headerArray[] = static::normalizeHeaderName($key) . ': ' . $value;
        }

        if ($toString) {
            $headerArray = implode("\r\n", $headerArray);
        }

        return $headerArray;
    }

    /**
     * Filter a header name to lowercase.
     *
     * @param   string $header Header name to normalize.
     *
     * @return  string  Normalized name.
     *
     * @since   3.0
     */
    public static function normalizeHeaderName($header): string
    {
        $filtered = str_replace('-', ' ', $header);
        $filtered = ucwords($filtered);

        return str_replace(' ', '-', $filtered);
    }

    /**
     * Prepare attachment headers to response object.
     *
     * @param  ResponseInterface $response The response object.
     * @param  string            $filename Download file name.
     *
     * @return  ResponseInterface
     */
    public static function prepareAttachmentHeaders(
        ResponseInterface $response,
        ?string $filename = null
    ): ResponseInterface {
        $response = $response->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Content-Encoding', 'none');

        if ($filename !== null) {
            $response = $response->withHeader(
                'Content-Disposition',
                static::attachmentContentDisposition($filename)
            );
        }

        return $response;
    }

    /**
     * downloadContentDisposition
     *
     * @param  string  $filename
     * @param  bool    $utf8
     *
     * @return  string
     *
     * @since  3.5.13
     */
    public static function attachmentContentDisposition(string $filename, bool $utf8 = true): string
    {
        return static::contentDisposition('attachment', $filename, $utf8);
    }

    /**
     * inlineContentDisposition
     *
     * @param  string  $filename
     * @param  bool    $utf8
     *
     * @return  string
     *
     * @since  3.5.15
     */
    public static function inlineContentDisposition(string $filename, bool $utf8 = true): string
    {
        return static::contentDisposition('inline', $filename, $utf8);
    }

    /**
     * contentDisposition
     *
     * @param  string  $type
     * @param  string  $filename
     * @param  bool    $utf8
     *
     * @return  string
     *
     * @since  3.5.15
     */
    public static function contentDisposition(string $type, string $filename, bool $utf8 = true): string
    {
        if ($utf8) {
            return sprintf("$type; filename*=utf-8''%s", rawurlencode(static::makeUtf8Safe($filename)));
        }

        return sprintf($type . '; filename="%s"', rawurlencode($filename));
    }

    /**
     * makeUtf8Safe
     *
     * @param string $file
     *
     * @return  false|string
     *
     * @since  3.5.14
     */
    private static function makeUtf8Safe(string $file): bool|string
    {
        $file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);

        return mb_ereg_replace("([\.]{2,})", '', $file);
    }
}
