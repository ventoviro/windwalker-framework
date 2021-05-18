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
use UnexpectedValueException;

/**
 * The HtmlResponse class.
 *
 * @since  3.0
 */
class JsonResponse extends TextResponse
{
    /**
     * Content type.
     *
     * @var  string
     */
    protected string $type = 'application/json';

    /**
     * Constructor.
     *
     * @param  string  $json     The JSON body data.
     * @param  int     $status   The status code.
     * @param  array   $headers  The custom headers.
     * @param  int     $options  Json encode options.
     */
    public function __construct($json = '', $status = 200, array $headers = [], int $options = 0)
    {
        parent::__construct(
            $this->encode($json, $options),
            $status,
            $headers
        );
    }

    /**
     * Encode json.
     *
     * @param  mixed  $data     The data to convert.
     * @param  int    $options  The json_encode() options flag.
     *
     * @return  mixed  Encoded json.
     * @throws \JsonException
     */
    protected function encode(mixed $data, int $options = 0): mixed
    {
        if ($data instanceof StreamInterface || is_resource($data)) {
            return $data;
        }

        // Check is already json string.
        if (is_string($data) && $data !== '') {
            $firstChar = $data[0];

            if (in_array($firstChar, ['[', '{', '"'])) {
                return $data;
            }
        }

        return json_encode($data, JSON_THROW_ON_ERROR | $options);
    }

    /**
     * withContent
     *
     * @param  mixed  $content
     *
     * @return  static
     * @throws InvalidArgumentException
     */
    public function withContent(string $content): static
    {
        return parent::withContent($this->encode($content));
    }
}
