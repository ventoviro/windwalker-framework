<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Response;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Stringable;
use Windwalker\Stream\NullStream;
use Windwalker\Stream\Stream;

/**
 * The RedirectResponse class.
 *
 * @since  3.0
 */
class RedirectResponse extends Response
{
    /**
     * Constructor.
     *
     * @param  string|Stringable  $uri      The redirect uri.
     * @param  int                $status   The status code.
     * @param  array              $headers  The custom headers.
     */
    public function __construct(string|Stringable $uri, $status = 303, array $headers = [])
    {
        if (!is_stringable($uri)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid URI type, string or UriInterface required, %s provided.',
                    gettype($uri)
                )
            );
        }

        $headers['location'] = [(string) $uri];

        parent::__construct(new NullStream(), $status, $headers);
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody(): StreamInterface
    {
        if (headers_sent()) {
            $url = $this->getHeaderLine('location');
            $html = "<script>document.location.href='$url';</script>\n";

            return Stream::fromString($html);
        }

        return $this->stream;
    }
}
