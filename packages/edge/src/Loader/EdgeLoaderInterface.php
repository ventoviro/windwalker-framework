<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Loader;

/**
 * Interface EdgeLoaderInterface
 *
 * @since  3.0
 */
interface EdgeLoaderInterface
{
    /**
     * load
     *
     * @param  string  $key
     *
     * @return  string
     */
    public function find(string $key): string;

    /**
     * loadFile
     *
     * @param  string  $path
     *
     * @return  string
     */
    public function load(string $path): string;

    /**
     * has
     *
     * @param  string  $key
     *
     * @return  bool
     */
    public function has(string $key): bool;
}
