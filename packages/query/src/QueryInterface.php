<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query;

/**
 * Interface QueryInterface
 */
interface QueryInterface
{
    /**
     * Make query object as SQL string.
     *
     * @return  string
     */
    public function __toString(): string;

    /**
     * Render this object as SQL string.
     *
     * @param  bool        $emulatePrepared  Replace bounded variables as values.
     * @param  array|null  $bounded          The bounded values to replace.
     *
     * @return  string  Rendered SQL string.
     */
    public function render(bool $emulatePrepared = false, ?array &$bounded = []): string;
}
