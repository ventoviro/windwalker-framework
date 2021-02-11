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
 * The DateTimeCast class.
 */
class DateTimeCast implements CastInterface
{
    /**
     * @inheritDoc
     */
    public function cast(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value)) {
            return \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        }

        return new \DateTimeImmutable($value);
    }

    /**
     * @inheritDoc
     */
    public function extract(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }
}
