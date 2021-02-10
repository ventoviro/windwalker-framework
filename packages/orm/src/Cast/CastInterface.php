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
    /**
     * Cast to php type or object.
     *
     * @param  string|null  $value
     *
     * @return  mixed
     */
    public function cast(?string $value): mixed;

    /**
     * Extract from php type or object to string or NULL for storing.
     *
     * @param  mixed  $value
     *
     * @return  string|null
     */
    public function extract(mixed $value): ?string;
}
