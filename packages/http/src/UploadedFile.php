<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use Windwalker\Stream\Stream;

/**
 * The UploadedFile class.
 *
 * @since  2.1
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * Property clientFilename.
     *
     * @var  string
     */
    protected $clientFilename;

    /**
     * Property clientMediaType.
     *
     * @var  string
     */
    protected $clientMediaType;

    /**
     * Property error.
     *
     * @var  int
     */
    protected $error;

    /**
     * Property file.
     *
     * @var  string
     */
    protected $file;

    /**
     * Property moved.
     *
     * @var  bool
     */
    protected $moved = false;

    /**
     * Property size.
     *
     * @var  int
     */
    protected $size;

    /**
     * Property stream.
     *
     * @var  StreamInterface
     */
    protected $stream;

    /**
     * PHP_SAPI store to support test mock.
     *
     * @var  string
     */
    protected $sapi;

    /**
     * Class init.
     *
     * @param  string|resource|StreamInterface  $file             The file source.
     * @param  int                              $size             The file size.
     * @param  int                              $error            The upload error status.
     * @param  string|null                      $clientFilename   The client filename.
     * @param  string|null                      $clientMediaType  The file media type.
     *
     */
    public function __construct(
        mixed $file,
        int $size = 0,
        int $error = UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ) {
        if ($error === UPLOAD_ERR_OK) {
            if (is_string($file)) {
                $this->file = $file;
                $this->stream = new Stream($file, Stream::MODE_READ_WRITE_FROM_BEGIN);
            } elseif (is_resource($file)) {
                $this->stream = new Stream($file);
            } elseif ($file instanceof StreamInterface) {
                $this->stream = $file;
            }

            if (!$this->file && !$this->stream) {
                throw new InvalidArgumentException('Invalid stream or file provided for UploadedFile');
            }
        }

        if (!is_int($size)) {
            throw new InvalidArgumentException('Size should be integer.');
        }

        if (!is_int($error) || 0 > $error || 8 < $error) {
            throw new InvalidArgumentException('Error status must be integer or an UPLOAD_ERR_* constant.');
        }

        if ($clientFilename !== null && !is_string($clientFilename)) {
            throw new InvalidArgumentException('Client filename must be null or string');
        }

        if ($clientMediaType !== null && !is_string($clientMediaType)) {
            throw new InvalidArgumentException('Media type must be null or a string');
        }

        $this->error = $error;
        $this->size = $size;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;

        $this->sapi = PHP_SAPI;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return  StreamInterface  Stream representation of the uploaded file.
     *
     * @throws RuntimeException in cases when no stream is available or can be
     *                           created.
     */
    public function getStream(): Stream|StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException('The file has already moved.');
        }

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        $this->stream = new Stream($this->file);

        return $this->stream;
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     *
     * @param  string  $targetPath  Path to which to move the uploaded file.
     *
     * @throws  InvalidArgumentException if the $path specified is invalid.
     * @throws  RuntimeException         on any error during the move operation, or on
     *                                    the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        $targetPath = (string) $targetPath;

        if (empty($targetPath)) {
            throw new InvalidArgumentException('Target path must be a non-empty string');
        }

        if ($this->moved) {
            throw new RuntimeException('Cannot move file, it has already moved!');
        }

        $sapi = $this->getSapi();

        // If we sent a stream or is CLI, use stream to write file.
        if (!$sapi || $sapi === 'cli' || !$this->file) {
            $this->writeFile($targetPath);
        } else {
            // If we sent a plain string as file path, use  move_uploaded_file()
            if (move_uploaded_file($this->file, $targetPath) === false) {
                throw new RuntimeException('Error moving uploaded file');
            }
        }
    }

    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return   int|null  The file size in bytes or null if unknown.
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see      http://php.net/manual/en/features.file-upload.errors.php
     *
     * @return   int  One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return   string|null  The filename sent by the client or null if none
     *                        was provided.
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return   string|null  The media type sent by the client or null if none
     *                        was provided.
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    /**
     * Write internal stream to given path
     *
     * @param  string  $path
     */
    protected function writeFile(string $path): void
    {
        $handle = fopen($path, Stream::MODE_READ_WRITE_RESET);

        if ($handle === false) {
            throw new RuntimeException('Unable to write to path: ' . $path);
        }

        $this->stream->rewind();

        while (!$this->stream->eof()) {
            fwrite($handle, $this->stream->read(4096));
        }

        fclose($handle);
    }

    /**
     * Method to get property Sapi
     *
     * @return  string
     */
    public function getSapi(): string
    {
        return $this->sapi;
    }

    /**
     * Method to set property sapi
     *
     * @param  string  $sapi
     *
     * @return  static  Return self to support chaining.
     */
    public function setSapi(string $sapi): static
    {
        $this->sapi = $sapi;

        return $this;
    }
}
