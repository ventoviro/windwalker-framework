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
 * Interface CastInterface
 */
interface CastInterface
{
    public const TYPE_CAST = 1;
    public const CONSTRUCTOR = 2;
    public const HYDRATE = 3;

    /**
     * Cast to php type or object.
     *
     * @param  mixed  $value
     *
     * @return  mixed
     */
    public function hydrate(mixed $value): mixed;

    /**
     * Extract from php type or object to string or NULL for storing.
     *
     * @param  mixed  $value
     *
     * @return  string|null
     */
    public function extract(mixed $value): ?string;
}
