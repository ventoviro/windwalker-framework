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
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

/**
 * The MustacheRenderer class.
 *
 * @since  2.0
 */
class MustacheRenderer extends AbstractEngineRenderer implements LayoutConverterInterface
{
    /**
     * Get default builder function.
     *
     * @return  Closure
     */
    public function getDefaultBuilder(): Closure
    {
        return function (array $options = []) {
            $engine = new Mustache_Engine($options['mustache'] ?? []);
            $engine->setLoader(
                new Mustache_Loader_FilesystemLoader(
                    $options['base_dir'] ?? $options['paths'][0] ?? '',
                    $options
                )
            );

            return $engine;
        };
    }

    /**
     * @inheritDoc
     */
    public function make(string $layout, array $options = []): Closure
    {
        /** @var Mustache_Engine $engine */
        $engine = $this->createEngine($options);

        return fn(array $data = []) => $engine->render($layout, $data);
    }

    public function handleLayout(string $layout, string $ext): string
    {
        if (str_ends_with($layout, $ext)) {
            return $layout;
        }

        return str_replace('.', '/', $layout) . '.' . $ext;
    }
}
