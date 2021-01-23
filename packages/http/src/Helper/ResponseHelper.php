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
 * The ResponseHelper class.
 *
 * @since  2.1
 *
 * todo: Support php8 types hint
 */
abstract class ResponseHelper
{
    /**
     * Status phrases.
     *
     * @var  array
     */
    protected static $phrases = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /**
     * Get status phrase by code.
     *
     * @param   integer $code Status code to get phrase.
     *
     * @return  string
     */
    public static function getPhrase($code): ?string
    {
        if (isset(static::$phrases[$code])) {
            return static::$phrases[$code];
        }

        return null;
    }

    /**
     * Validate a status code.
     *
     * @param   int|string $code
     *
     * @return  boolean  Valid or not.
     */
    public static function validateStatus($code): bool
    {
        $code = (int) $code;

        return ($code >= 100 && $code < 600);
    }

    /**
     * inRange
     *
     * @param int      $code
     * @param int      $start
     * @param int|null $end
     *
     * @return  bool
     *
     * @since  3.5.19
     */
    public static function inRange(int $code, int $start, ?int $end = null): bool
    {
        if ($end === null) {
            $end = $start + 100;
        }

        if ($end < $start) {
            throw new \InvalidArgumentException('Range end should larger than start.');
        }

        return $code >= $start && $code < $end;
    }

    /**
     * isSuccess
     *
     * @param int $code
     *
     * @return  bool
     *
     * @since  3.5.19
     */
    public static function isSuccess(int $code): bool
    {
        return static::inRange($code, 200);
    }

    /**
     * isRedirect
     *
     * @param int $code
     *
     * @return  bool
     *
     * @since  3.5.19
     */
    public static function isRedirect(int $code): bool
    {
        return static::inRange($code, 300);
    }

    /**
     * isClientError
     *
     * @param int $code
     *
     * @return  bool
     *
     * @since  3.5.19
     */
    public static function isClientError(int $code): bool
    {
        return static::inRange($code, 400);
    }

    /**
     * isServerError
     *
     * @param int $code
     *
     * @return  bool
     *
     * @since  3.5.19
     */
    public static function isServerError(int $code): bool
    {
        return static::inRange($code, 500);
    }
}
