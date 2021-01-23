<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

namespace Windwalker\Filesystem\Iterator;

/**
 * Recursive directory iterator that is working during recursive iteration.
 *
 * Recursive iteration is broken on PHP < 5.5.23 and on PHP 5.6 < 5.6.7.
 *
 * @since  1.0
 * @since  3.0 Removed support for seek(), added \RecursiveDirectoryIterator
 *             base class, adapted API to match \RecursiveDirectoryIterator
 * @since  3.1 Slashes are normalized to forward slashes on Windows
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RecursiveDirectoryIterator extends \RecursiveDirectoryIterator
{
    /**
     * @var bool
     */
    private $normalizeKey;

    /**
     * @var bool
     */
    private $normalizeCurrent;

    /**
     * {@inheritdoc}
     */
    public function __construct($path, $flags = 0)
    {
        parent::__construct($path, $flags);

        // Normalize slashes on Windows
        $this->normalizeKey = '\\' === DIRECTORY_SEPARATOR && !($flags & self::KEY_AS_FILENAME);
        $this->normalizeCurrent = '\\' === DIRECTORY_SEPARATOR && ($flags & self::CURRENT_AS_PATHNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren(): static
    {
        return new static($this->getPathname(), $this->getFlags());
    }

    /**
     * {@inheritdoc}
     */
    public function key(): array|string
    {
        $key = parent::key();

        if ($this->normalizeKey) {
            $key = str_replace('\\', '/', $key);
        }

        return $key;
    }

    /**
     * {@inheritdoc}
     */
    public function current(): array|\SplFileInfo|string|\RecursiveDirectoryIterator
    {
        $current = parent::current();

        if ($this->normalizeCurrent) {
            $current = str_replace('\\', '/', $current);
        }

        return $current;
    }
}
