<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Renderer;

use Closure;

/**
 * Interface RendererFactoryInterface
 */
interface TemplateFactoryInterface
{
    /**
     * Make template engine.
     *
     * @param  string  $layout
     * @param  array   $options
     *
     * @return  Closure
     */
    public function make(string $layout, array $options = []): Closure;
}
