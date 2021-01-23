<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Stub;

use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Response\AbstractContentTypeResponse;
use Windwalker\Stream\Stream;

/**
 * The StubContentTypeResponse class.
 *
 * @since  3.0
 */
class StubContentTypeResponse extends AbstractContentTypeResponse
{
    /**
     * Handle body to stream object.
     *
     * @param  string  $body  The body data.
     *
     * @return Stream|StreamInterface Converted to stream object.
     */
    protected function handleBody(string $body): Stream|StreamInterface
    {
        $stream = new Stream('php://memory', 'rw+');
        $stream->write($body);
        $stream->rewind();

        return $stream;
    }
}
