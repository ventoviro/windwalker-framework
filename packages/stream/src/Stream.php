<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Stream;

use Psr\Http\Message\StreamInterface;

/**
 * The Stream class.
 *
 * @since  2.1
 */
class Stream implements StreamInterface
{
    public const MODE_READ_ONLY_FROM_BEGIN = 'rb';

    public const MODE_READ_WRITE_FROM_BEGIN = 'rb+';

    public const MODE_WRITE_ONLY_RESET = 'wb';

    public const MODE_READ_WRITE_RESET = 'wb+';

    public const MODE_WRITE_ONLY_FROM_END = 'ab';

    public const MODE_READ_WRITE_FROM_END = 'ab+';

    /**
     * Stream resource.
     *
     * @var resource
     */
    protected mixed $resource;

    /**
     * Stream resource.
     *
     * @var string|resource
     */
    protected mixed $stream;

    /**
     * fromString
     *
     * @param  string  $string
     * @param  string  $mode
     *
     * @return  static
     */
    public static function fromString(string $string, string $mode = self::MODE_READ_WRITE_FROM_BEGIN): static
    {
        $stream = new static(null, $mode);
        $stream->write($string);

        return $stream;
    }

    /**
     * fromString
     *
     * @param  string  $file
     * @param  string  $mode
     *
     * @return  static
     */
    public static function fromFilePath(string $file, string $mode = self::MODE_READ_WRITE_RESET): static
    {
        return new static(fopen($file, $mode));
    }

    /**
     * wrap
     *
     * @param  string|resource|StreamInterface  $stream
     * @param  string                           $mode
     *
     * @return  StreamInterface
     */
    public static function wrap($stream, string $mode = self::MODE_READ_WRITE_FROM_BEGIN): StreamInterface
    {
        if ($stream instanceof StreamInterface) {
            return $stream;
        }

        return new Stream($stream, $mode);
    }

    /**
     * Class init.
     *
     * @param  string|resource  $stream  The stream resource cursor.
     * @param  string           $mode    Mode with which to open stream
     */
    public function __construct($stream = null, string $mode = self::MODE_READ_WRITE_FROM_BEGIN)
    {
        $this->attach($stream ?? 'php://memory', $mode);
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString(): string
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            $this->rewind();

            return $this->getContents();
        } catch (\Exception $e) {
            return (string) $e;
        }
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        if (!$this->resource) {
            return;
        }

        $resource = $this->detach();

        fclose($resource);
    }

    /**
     * Method to attach resource into object.
     *
     * @param  string|resource  $stream  The stream resource cursor.
     * @param  string           $mode    Mode with which to open stream
     *
     * @return  static Return self to support chaining.
     */
    public function attach(mixed $stream, string $mode = 'r'): static
    {
        $this->stream = $stream;

        if (is_resource($stream)) {
            $this->resource = $stream;
        } elseif (is_string($stream)) {
            $this->resource = fopen($stream, $mode);
        } elseif ($stream !== false) {
            throw new \InvalidArgumentException('Invalid resource.');
        }

        return $this;
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $resource = $this->resource;

        $this->resource = null;
        $this->stream   = null;

        return $resource;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize(): ?int
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        $stats = fstat($this->resource);

        return $stats['size'];
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell(): int
    {
        if (!is_resource($this->resource)) {
            throw new \RuntimeException('No resource available.');
        }

        $result = ftell($this->resource);

        if (!is_int($result)) {
            throw new \RuntimeException('Error occurred during tell operation');
        }

        return $result;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof(): bool
    {
        if (!is_resource($this->resource)) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);

        return $meta['seekable'];
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     *
     * @param  int  $offset  Stream offset
     * @param  int  $whence  Specifies how the cursor position will be calculated
     *                       based on the seek offset. Valid values are identical to the built-in
     *                       PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *                       offset bytes SEEK_CUR: Set position to current location plus offset
     *                       SEEK_END: Set position to end-of-stream plus offset.
     *
     * @return boolean
     *
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET): bool
    {
        if (!is_resource($this->resource)) {
            throw new \RuntimeException('No resource available.');
        }

        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }

        $result = fseek($this->resource, $offset, $whence);

        if ($result !== 0) {
            throw new \RuntimeException('Error seeking within stream');
        }

        return true;
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @throws \RuntimeException on failure.
     * @link http://www.php.net/manual/en/function.fseek.php
     * @see  seek()
     */
    public function rewind(): bool
    {
        return $this->seek(0);
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);

        return is_writable($meta['uri']);
    }

    /**
     * Write data to the stream.
     *
     * @param  string  $string  The string that is to be written.
     *
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string): int
    {
        if (!is_resource($this->resource)) {
            throw new \RuntimeException('No resource available.');
        }

        $result = fwrite($this->resource, $string);

        if ($result === false) {
            throw new \RuntimeException('Error writing to stream');
        }

        return $result;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return (strpos($mode, 'r') !== false || strpos($mode, '+') !== false);
    }

    /**
     * Read data from the stream.
     *
     * @param  int  $length  Read up to $length bytes from the object and return
     *                       them. Fewer than $length bytes may be returned if underlying stream
     *                       call returns fewer bytes.
     *
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length): string
    {
        if (!is_resource($this->resource)) {
            throw new \RuntimeException('No resource available.');
        }

        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }

        $result = fread($this->resource, $length);

        if ($result === false) {
            throw new \RuntimeException('Error reading stream');
        }

        return $result;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents(): string
    {
        if (!$this->isReadable()) {
            return '';
        }

        $result = stream_get_contents($this->resource);

        if ($result === false) {
            throw new \RuntimeException('Error reading from stream');
        }

        return $result;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     *
     * @param  string  $key  Specific metadata to retrieve.
     *
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null): mixed
    {
        $metadata = stream_get_meta_data($this->resource);

        if ($key === null) {
            return $metadata;
        }

        if (!array_key_exists($key, $metadata)) {
            return null;
        }

        return $metadata[$key];
    }

    /**
     * Method to get property Resource
     *
     * @return  resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
