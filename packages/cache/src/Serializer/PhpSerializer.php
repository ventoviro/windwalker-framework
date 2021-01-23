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
 * Class SerializeHandler
 *
 * @since 2.0
 */
class PhpSerializer implements SerializerInterface
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
        return serialize($data);
    }

    /**
     * Decode data.
     *
     * @param  string  $data
     *
     * @return mixed
     */
    public function unserialize(string $data): mixed
    {
        return unserialize($data);
    }
}
