<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

namespace Windwalker\Filesystem\Iterator;

use Iterator;
use Windwalker\Filesystem\Glob;

/**
 * Filters an iterator by a glob.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @see    Glob
 */
class GlobFilterIterator extends RegexFilterIterator
{
    /**
     * Creates a new iterator.
     *
     * @param string   $glob          The canonical glob.
     * @param Iterator $innerIterator The filtered iterator.
     * @param int      $mode          A bitwise combination of the mode constants.
     * @param int      $flags         A bitwise combination of the flag constants
     *                                in {@link Glob}.
     */
    public function __construct($glob, Iterator $innerIterator, $mode = self::FILTER_VALUE, $flags = 0)
    {
        parent::__construct(
            Glob::toRegEx($glob, $flags),
            Glob::getStaticPrefix($glob, $flags),
            $innerIterator,
            $mode
        );
    }
}
