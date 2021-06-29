<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

/**
 * The JsonCast class.
 */
class JsonCast implements CastInterface
{
    /**
     * @inheritDoc
     */
    public function hydrate(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if ($value === '' || $value === null) {
            return null;
        }

        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @inheritDoc
     * @throws \JsonException
     */
    public function extract(mixed $value): ?string
    {
        return is_json($value) ? $value : json_encode($value ?? '', JSON_THROW_ON_ERROR);
    }
}
