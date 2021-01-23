<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Serializer;

/**
 * The RawDataHandler class.
 *
 * @since  2.1.2
 */
class RawSerializer implements SerializerInterface
{
    /**
     * Encode data.
     *
     * @param  mixed  $data
     *
     * @return string|null
     */
    public function serialize($data): ?string
    {
        return $data;
    }

    /**
     * Decode data.
     *
     * @param  string  $data
     *
     * @return string|null
     */
    public function unserialize(string $data): ?string
    {
        return $data;
    }
}
