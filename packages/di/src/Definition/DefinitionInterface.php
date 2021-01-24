<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use Windwalker\DI\Container;

/**
 * Interface DefinitionInterface
 */
interface DefinitionInterface
{
    /**
     * Resolve this definition.
     *
     * @param  Container  $container  The Container object.
     *
     * @return
     */
    public function resolve(Container $container);

    /**
     * Set new value or factory callback to this definition.
     *
     * @param  mixed  $value  Value or callable.
     *
     * @return  void
     */
    public function set(mixed $value): void;
}
