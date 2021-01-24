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
use League\Plates\Engine as Plates;

/**
 * The PlatesRenderer class.
 *
 * @since  2.0.9
 */
class PlatesRenderer extends AbstractEngineRenderer implements LayoutConverterInterface
{
    /**
     * Get default builder function.
     *
     * @return  Closure
     */
    public function getDefaultBuilder(): Closure
    {
        return fn(array $options = []) => $this->createPlates(
            $options['base_dir'] ?? $options['paths'][0] ?? '',
            $options['file_extensions'][0] ?? 'phtml'
        );
    }

    protected function createPlates($directory = null, $fileExtension = 'php'): Plates
    {
        if (method_exists(Plates::class, 'create')) {
            return Plates::create($directory, $fileExtension);
        }

        return new Plates($directory, $fileExtension);
    }

    /**
     * @inheritDoc
     */
    public function make(string $layout, array $options = []): Closure
    {
        /** @var Plates $engine */
        $engine = $this->createEngine($options);

        return fn (array $data) => $engine->render($layout, $data, $options['attributes'] ?? []);
    }

    public function handleLayout(string $layout, string $ext): string
    {
        if (str_ends_with($layout, $ext)) {
            return $layout;
        }

        return str_replace('.', '/', $layout) . '.' . $ext;
    }
}
