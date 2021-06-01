<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

use Windwalker\Utilities\TypeCast;

/**
 * The TimestampCast class.
 */
class TimestampCast implements CastInterface
{
    /**
     * @inheritDoc
     */
    public function cast(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!is_numeric($value)) {
            $value = (new \DateTimeImmutable($value))->getTimestamp();
        }

        return TypeCast::tryNumeric($value);
    }

    /**
     * @inheritDoc
     */
    public function extract(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('U', $value);

        return $date->format('Y-m-d H:i:s');
    }
}
