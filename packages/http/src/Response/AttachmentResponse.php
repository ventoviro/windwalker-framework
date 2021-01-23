<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Response;

use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Stream\Stream;

/**
 * The AttachmentResponse class.
 *
 * @since  3.2
 */
class AttachmentResponse extends Response
{
    /**
     * Constructor.
     *
     * @param  string $body    The body data.
     * @param  int    $status  The status code.
     * @param  array  $headers The custom headers.
     */
    public function __construct($body = 'php://temp', $status = 200, array $headers = [])
    {
        parent::__construct($body, $status, $headers);

        $res = HeaderHelper::prepareAttachmentHeaders($this);

        $this->headers = $res->headers;

        foreach (array_keys($this->headers) as $header) {
            $this->headerNames[strtolower($header)] = $header;
        }
    }

    /**
     * withFile
     *
     * @param string $file
     *
     * @return  static
     * @throws \InvalidArgumentException
     */
    public function withFile(string $file): static
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException('File: ' . $file . ' not exists.');
        }

        return $this->withFileStream(new Stream($file));
    }

    /**
     * withFileData
     *
     * @param string $data
     *
     * @return  static
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function withFileData(string $data): static
    {
        $stream = new Stream('php://temp', Stream::MODE_READ_WRITE_RESET);

        $stream->write($data);
        $stream->rewind();

        return $this->withFileStream($stream);
    }

    /**
     * withFileStream
     *
     * @param StreamInterface $stream
     *
     * @return  static
     * @throws \InvalidArgumentException
     */
    protected function withFileStream(StreamInterface $stream): static
    {
        return $this->withBody($stream)->withHeader('Content-Length', (string) $stream->getSize());
    }

    /**
     * withFilename
     *
     * @param string $filename
     *
     * @return  static
     * @throws \InvalidArgumentException
     */
    public function withFilename(string $filename): static
    {
        return $this->withHeader(
            'Content-Disposition',
            HeaderHelper::attachmentContentDisposition($filename)
        );
    }
}
