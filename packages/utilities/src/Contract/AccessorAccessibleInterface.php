<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Contract;

/**
 * Interface AccessorAccessibleInterface
 */
interface AccessorAccessibleInterface
{
    /**
     * Get value from this object.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function &get(mixed $key): mixed;

    /**
     * Set value to this object.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     *
     * @return  static
     */
    public function set(mixed $key, mixed $value): static;

    /**
     * Set value default if not exists.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function def(mixed $key, mixed $default): static;

    /**
     * Check a key exists or not.
     *
     * @param  mixed  $key
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function has(mixed $key): bool;

    /**
     * Creates a copy of storage.
     *
     * @param  bool  $recursive
     *
     * @param  bool  $onlyDumpable
     *
     * @return array
     */
    public function dump(bool $recursive = false, bool $onlyDumpable = false): array;
}
