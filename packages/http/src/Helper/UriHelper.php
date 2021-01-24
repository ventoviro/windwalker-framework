<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Helper;

/**
 * Uri Helper
 *
 * This class provides an UTF-8 safe version of parse_url().
 *
 * This class is a fork from Joomla Uri.
 *
 * @since  2.0
 */
class UriHelper
{
    /**
     * Sub-delimiters used in query strings and fragments.
     *
     * @const string
     */
    public const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

    /**
     * Unreserved characters used in paths, query strings, and fragments.
     *
     * @const string
     */
    public const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

    /**
     * Build a query from a array (reverse of the PHP parse_str()).
     *
     * @param  array  $params  The array of key => value pairs to return as a query string.
     *
     * @return  string  The resulting query string.
     *
     * @see     parse_str()
     * @since   2.0
     */
    public static function buildQuery(array $params): string
    {
        return http_build_query($params, '', '&');
    }

    /**
     * Does a UTF-8 safe version of PHP parse_url function
     *
     * @param  string  $url  URL to parse
     *
     * @return  mixed  Associative array or false if badly formed URL.
     *
     * @see     http://us3.php.net/manual/en/function.parse-url.php
     * @since   2.0
     */
    public static function parseUrl(string $url): array|bool
    {
        $result = false;

        // Build arrays of values we need to decode before parsing
        $entities     = [
            '%21',
            '%2A',
            '%27',
            '%28',
            '%29',
            '%3B',
            '%3A',
            '%40',
            '%26',
            '%3D',
            '%24',
            '%2C',
            '%2F',
            '%3F',
            '%23',
            '%5B',
            '%5D',
        ];
        $replacements = ['!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "$", ",", "/", "?", "#", "[", "]"];

        // Create encoded URL with special URL characters decoded so it can be parsed
        // All other characters will be encoded
        $encodedURL = str_replace($entities, $replacements, urlencode($url));

        // Parse the encoded URL
        $encodedParts = parse_url($encodedURL);

        // Now, decode each value of the resulting array
        if ($encodedParts) {
            foreach ($encodedParts as $key => $value) {
                if (!is_string($value)) {
                    $result[$key] = $value;
                    continue;
                }

                $result[$key] = urldecode(str_replace($replacements, $entities, (string) $value));
            }
        }

        return $result;
    }

    /**
     * parseQuery
     *
     * @param  string  $query
     *
     * @return  mixed
     */
    public static function parseQuery(string $query): array
    {
        parse_str($query, $vars);

        return $vars;
    }

    /**
     * filterScheme
     *
     * @param  string  $scheme
     *
     * @return  string
     */
    public static function filterScheme(string $scheme): string
    {
        $scheme = strtolower($scheme);
        $scheme = preg_replace('#:(//)?$#', '', $scheme);

        if (empty($scheme)) {
            return '';
        }

        return $scheme;
    }

    /**
     * Filter a query string to ensure it is propertly encoded.
     *
     * Ensures that the values in the query string are properly urlencoded.
     *
     * @param  string  $query
     *
     * @return  string
     */
    public static function filterQuery(string $query): string
    {
        if (!empty($query) && str_starts_with($query, '?')) {
            $query = substr($query, 1);
        }

        $parts = explode('&', $query);

        foreach ($parts as $index => $part) {
            [$key, $value] = static::splitQueryValue($part);

            if ($value === null) {
                $parts[$index] = static::filterQueryOrFragment($key);

                continue;
            }

            $parts[$index] = sprintf(
                '%s=%s',
                static::filterQueryOrFragment($key),
                static::filterQueryOrFragment($value)
            );
        }

        return implode('&', $parts);
    }

    /**
     * Split a query value into a key/value tuple.
     *
     * @param  string  $value
     *
     * @return  array  A value with exactly two elements, key and value
     */
    public static function splitQueryValue(string $value): array
    {
        $data = explode('=', $value, 2);

        if (1 === count($data)) {
            $data[] = null;
        }

        return $data;
    }

    /**
     * Filter a query string key or value, or a fragment.
     *
     * @param  string  $value
     *
     * @return  string
     */
    public static function filterQueryOrFragment(string $value): string
    {
        return preg_replace_callback(
            '/(?:[^' . static::CHAR_UNRESERVED . static::CHAR_SUB_DELIMS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            function ($matches) {
                return rawurlencode($matches[0]);
            },
            $value
        );
    }

    /**
     * Filter a fragment value to ensure it is properly encoded.
     *
     * @param  string  $fragment
     *
     * @return  string
     */
    public static function filterFragment(string $fragment): string
    {
        if (null === $fragment) {
            $fragment = '';
        }

        if (!empty($fragment) && str_starts_with($fragment, '#')) {
            $fragment = substr($fragment, 1);
        }

        return static::filterQueryOrFragment($fragment);
    }

    /**
     * Filters the path of a URI to ensure it is properly encoded.
     *
     * @param  string  $path
     *
     * @return  string
     */
    public static function filterPath(string $path): string
    {
        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            function ($matches) {
                return rawurlencode($matches[0]);
            },
            $path
        );
    }

    /**
     * decode
     *
     * @param  array|string  $string
     *
     * @param  bool          $raw
     *
     * @return  array|string
     */
    public static function decode(array|string $string, bool $raw = false): array|string
    {
        if (is_array($string)) {
            foreach ($string as $k => $substring) {
                $string[$k] = static::decode($substring, $raw);
            }
        } else {
            $func = $raw ? 'rawurldecode' : 'urldecode';

            $string = $func($string);
        }

        return $string;
    }

    /**
     * encode
     *
     * @param  array|string  $string  $string
     *
     * @return  array|string
     */
    public static function encode(array|string $string, bool $raw = false): array|string
    {
        if (is_array($string)) {
            foreach ($string as $k => $substring) {
                $string[$k] = static::encode($substring, $raw);
            }
        } else {
            $func = $raw ? 'rawurlencode' : 'urlencode';

            $string = $func($string);
        }

        return $string;
    }
}
