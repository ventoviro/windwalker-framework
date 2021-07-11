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
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory as BladeEnvironment;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;

/**
 * The BladeRenderer class.
 *
 * @since  2.0
 */
class BladeRenderer extends AbstractEngineRenderer
{
    /**
     * @inheritDoc
     */
    public function make(string $layout, array $options = []): Closure
    {
        /** @var BladeEnvironment $engine */
        $engine = $this->createEngine($options);

        return fn(array $data = []) => $engine->make($layout, $data)->render();
    }

    /**
     * Get default builder function.
     *
     * @return  Closure
     */
    public function getDefaultBuilder(): Closure
    {
        return function (array $options = []) {
            $fs = $this->createFilesystem();

            return new BladeEnvironment(
                $this->createResolver($fs, $options['cache_path'] ?? null),
                $this->createFinder($fs, $options['paths'] ?? []),
                $this->createDispatcher()
            );
        };
    }

    /**
     * Method to get property Filesystem
     *
     * @return  Filesystem
     */
    public function createFilesystem(): Filesystem
    {
        return new Filesystem();
    }

    /**
     * Method to get property Finder
     *
     * @param  Filesystem|null  $fs
     * @param  array            $paths
     *
     * @return  FileViewFinder
     */
    public function createFinder(?Filesystem $fs = null, array $paths = []): FileViewFinder
    {
        return new FileViewFinder($fs ?? $this->createFilesystem(), $paths);
    }

    /**
     * Method to get property Resolver
     *
     * @param  Filesystem|null  $fs
     *
     * @param  string|null      $cachePath
     *
     * @return  EngineResolver
     */
    public function createResolver(?Filesystem $fs = null, ?string $cachePath = null): EngineResolver
    {
        $resolver = new EngineResolver();

        $resolver->register(
            'blade',
            fn() => $this->createCompiler($fs, $cachePath)
        );

        return $resolver;
    }

    /**
     * Method to get property Dispatcher
     *
     * @return  Dispatcher
     */
    public function createDispatcher(): Dispatcher
    {
        return new Dispatcher();
    }

    /**
     * Method to get property Compiler
     *
     * @param  Filesystem|null  $fs
     * @param  string|null      $cachePath
     *
     * @return  CompilerEngine
     */
    public function createCompiler(?Filesystem $fs = null, ?string $cachePath = null): CompilerEngine
    {
        if (!$cachePath) {
            throw new InvalidArgumentException('Please set cache_path to options.');
        }

        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        return new CompilerEngine(new BladeCompiler($fs ?? $this->createFilesystem(), $cachePath));
    }
}
