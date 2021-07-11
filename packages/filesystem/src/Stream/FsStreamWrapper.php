<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

// phpcs:disable

declare(strict_types=1);

namespace Windwalker\Filesystem\Stream;

use Exception;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * This class is for test use, do not use in production code.
 *
 * @deprecated
 */
class FsStreamWrapper
{
    /**
     * @var string
     */
    private static $protocol;

    /**
     * @var bool
     */
    private static $registered = false;

    /**
     * @var static
     */
    protected static $instance;

    /**
     * @var StreamInterface[]
     */
    private static $streams = [];

    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * fetchUri
     *
     * @param  string  $path
     *
     * @return  string
     */
    public static function fetchUri(string $path): string
    {
        return static::$protocol . '://' . $path;
    }

    /**
     * addStream
     *
     * @param  string           $key
     * @param  StreamInterface  $stream
     *
     * @return  callable  Remove stream callback
     */
    public static function addStream(string $key, StreamInterface $stream): callable
    {
        static::$streams[$key] = $stream;

        return static function () use ($key) {
            static::removeStream($key);
        };
    }

    /**
     * removeStream
     *
     * @param  string  $key
     *
     * @return  void
     */
    public static function removeStream(string $key): void
    {
        unset(static::$streams[$key]);
    }

    /**
     * register
     *
     * @param  string|null  $protocol
     *
     * @return  bool
     */
    public static function register(?string $protocol = null): bool
    {
        if (static::$registered) {
            return true;
        }

        try {
            static::$protocol = $protocol ?? 'wwfs' . random_int(1000, 9999);
        } catch (Exception $e) {
            throw new RuntimeException('random_int caused error: ' . $e->getMessage(), $e->getCode(), $e);
        }

        $result = stream_wrapper_register(static::$protocol, static::class, 0);

        if ($result) {
            static::$registered = true;
        }

        return $result;
    }

    /**
     * unregister
     *
     * @return  bool
     */
    public static function unregister(): bool
    {
        $result = stream_wrapper_unregister(static::$protocol);

        if ($result) {
            static::$registered = false;
        }

        return $result;
    }

    public function stream_open($path, $mode, $options, &$opened_path): bool
    {
        [, $path] = explode('://', $path);

        $this->stream = static::$streams[$path];

        $opened_path = $path;

        unset(static::$streams[$path]);

        return true;
    }

    public function stream_read($count): string
    {
        return $this->stream->read($count);
    }

    public function stream_write($data): int
    {
        return $this->stream->write($data);
    }

    public function stream_tell(): int
    {
        return $this->stream->tell();
    }

    public function stream_eof(): bool
    {
        return $this->stream->eof();
    }

    public function stream_seek($offset, $whence)
    {
        return $this->stream->seek($offset, $whence);
    }

    public function stream_metadata($path, $option, $var)
    {
        return $this->stream->getMetadata($path);
    }

    public function stream_stat(): bool|array
    {
        // Just fake stat
        return stat(__FILE__);
    }

    /**
     * Method to get property Protocol
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getProtocol(): string
    {
        return static::$protocol;
    }
}
