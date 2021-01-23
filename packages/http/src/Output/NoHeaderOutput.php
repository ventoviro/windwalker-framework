<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Output;

/**
 * The NoHeaderOutput class.
 *
 * @since  3.0
 */
class NoHeaderOutput extends Output
{
    /**
     * header
     *
     * @param  string    $string
     * @param  bool      $replace
     * @param  int|null  $code
     *
     * @return  $this
     */
    public function header(string $string, bool $replace = true, int $code = null): static
    {
        return $this;
    }
}
