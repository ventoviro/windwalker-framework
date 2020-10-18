<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Renderer;

/**
 * Interface EngineFactoryInterface
 */
interface ExtendableRendererInterface
{
    /**
     * Extends engine after created, this is similar a decorator.
     *
     * @param  callable  $callable
     *
     * @return  static  Retrun self to support chaining.
     */
    public function extend(callable $callable): static;
}
