<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Scalars;

/**
 * The ScalarsFactory class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ScalarsFactory
{
    /**
     * fromNative
     *
     * @param  mixed  $value
     *
     * @return  ArrayObject|StringObject|mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function fromNative(mixed $value): mixed
    {
        if (is_array($value)) {
            return new ArrayObject($value);
        }

        if (is_scalar($value)) {
            return new StringObject($value);
        }

        return $value;
    }
}
