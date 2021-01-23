<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Renderer;

use SplPriorityQueue;
use Windwalker\Edge\Exception\LayoutNotFoundException;
use Windwalker\Filesystem\Path;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Assert\LogicAssert;
use Windwalker\Utilities\Cache\InstanceCacheTrait;
use Windwalker\Utilities\Classes\ObjectBuilderAwareTrait;
use Windwalker\Utilities\Iterator\PriorityQueue;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\Str;

/**
 * Class AbstractRenderer
 *
 * @since 2.0
 */
class CompositeRenderer implements RendererInterface, TemplateFactoryInterface, ExtendableRendererInterface
{
    use ObjectBuilderAwareTrait;
    use InstanceCacheTrait;
    use OptionAccessTrait;

    protected array $factories = [
        'edge' => [
            EdgeRenderer::class,
            ['edge.php']
        ],
        'blade' => [
            BladeRenderer::class,
            ['blade.php']
        ],
        'plates' => [
            PlatesRenderer::class,
            ['php']
        ],
        'mustache' => [
            MustacheRenderer::class,
            ['mustache']
        ],
        'twig' => [
            TwigRenderer::class,
            ['twig']
        ],
    ];

    protected array $aliases = [];

    /**
     * Property paths.
     *
     * @var PriorityQueue
     */
    protected PriorityQueue $paths;

    /**
     * Class init.
     *
     * @param  string|array|SplPriorityQueue  $paths
     * @param  array                   $options
     */
    public function __construct(SplPriorityQueue|string|array $paths = [], array $options = [])
    {
        $this->setPaths($paths);

        $this->prepareOptions(
            [],
            $options
        );
    }

    /**
     * @inheritDoc
     */
    public function make(string $layout, array $options = []): \Closure
    {
        $options = Arr::mergeRecursive($this->getOptions(), $options);

        if (is_file($layout)) {
            $path = dirname($layout);
            $filename = Path::getFilename($layout);
        } else {
            $file = $this->findFile($layout);

            if (!$file) {
                throw new LayoutNotFoundException(
                    sprintf(
                        'Layout: %s not found in paths: %s',
                        $layout,
                        implode("\n| ", $this->dumpPaths())
                    )
                );
            }

            [$fullPath, $path, $filename] = $file;
        }

        [$type, , $extension] = $this->matchExtension($filename);
        $renderer = $this->getRenderer($type);

        if ($renderer instanceof LayoutConverterInterface) {
            $layout = $renderer->handleLayout($layout, $extension);
        }

        $options['paths'] = $this->dumpPaths();
        $options['base_dir'] = $path;

        return $renderer->make($layout, $options);
    }

    /**
     * @inheritDoc
     */
    public function render(string $layout, array $data = [], array $options = []): string
    {
        return $this->make($layout, $options)($data);
    }

    protected function matchExtension(string $layout): array
    {
        foreach ($this->getFactories() as $type => [$factory, $extensions]) {
            foreach ($extensions as $extension) {
                if (str_ends_with($layout, '.' . $extension)) {
                    return [$type, $factory, $extension];
                }
            }
        }

        throw new LayoutNotFoundException('There\'s no matched layout file found.');
    }

    /**
     * finFile
     *
     * @param  string       $layout
     *
     * @return array|null
     */
    public function findFile(string $layout): ?array
    {
        $layout = $this->resolveAlias($layout);

        $paths = clone $this->getPaths();

        $slashedLayout = str_replace('.', '/', $layout);

        foreach ($paths as $path) {
            // Match full name
            $filePath = $path . '/' . $layout;

            if (is_file($filePath)) {
                return $this->getFoundFileInfo($filePath, $path);
            }

            foreach ($this->getSupportedExtensions() as $ext) {
                $filePath = $path . '/' . $slashedLayout . '.' . $ext;

                if (is_file($filePath)) {
                    return $this->getFoundFileInfo($filePath, $path);
                }
            }
        }

        return null;
    }

    /**
     * has
     *
     * @param  string  $layout
     *
     * @return  bool
     *
     * @since  3.5.2
     */
    public function has(string $layout): bool
    {
        return $this->findFile($layout) !== null;
    }

    /**
     * getPaths
     *
     * @return  PriorityQueue
     */
    public function getPaths(): PriorityQueue
    {
        return $this->paths;
    }

    /**
     * setPaths
     *
     * @param  array|string|SplPriorityQueue  $paths
     *
     * @return static Return self to support chaining.
     */
    public function setPaths(array|string|SplPriorityQueue $paths): static
    {
        if ($paths instanceof SplPriorityQueue) {
            $paths = new PriorityQueue($paths);
        }

        if (!$paths instanceof PriorityQueue) {
            $priority = new PriorityQueue();

            foreach ((array) $paths as $i => $path) {
                $priority->insert(Path::normalize($path), 100 - ($i * 10));
            }

            $paths = $priority;
        }

        $this->paths = $paths;

        return $this;
    }

    /**
     * addPath
     *
     * @param  string   $path
     * @param  integer  $priority
     *
     * @return  static
     */
    public function addPath(string $path, int $priority = 100): static
    {
        $this->paths->insert($path, $priority);

        return $this;
    }

    /**
     * clearPaths
     *
     * @return  static
     */
    public function clearPaths(): static
    {
        $this->setPaths([]);

        return $this;
    }

    /**
     * dumpPaths
     *
     * @return  array
     */
    public function dumpPaths(): array
    {
        $paths = clone $this->paths;

        $return = [];

        foreach ($paths as $path) {
            $return[] = $path;
        }

        return $return;
    }

    public function getRenderer(string $type, array $options = []): TemplateFactoryInterface
    {
        return $this->once('renderer.' . $type, function () use ($options, $type) {
            [$factory, $exts] = $this->getFactory($type);

            return $this->getObjectBuilder()->createObject($factory, $options);
        });
    }

    public function addFactory(string $type, string $class, array $extensions = []): void
    {
        LogicAssert::assert(
            is_subclass_of($class, TemplateFactoryInterface::class),
            sprintf(
                'Renderer factory should be sub class of %s, %s given.',
                TemplateFactoryInterface::class,
                $class
            )
        );

        $this->factories[$type] = [$class, $extensions];
    }

    public function addFileExtensions(string $type, array|string $extensions): void
    {
        LogicAssert::assert(
            (bool) $this->getFactory($type),
            sprintf(
                'Factory %s not registered.',
                $type
            )
        );

        $extensions = (array) $extensions;

        $this->factories[$type][1] = array_unique(
            array_merge($this->factories[$type][1], $extensions)
        );
    }

    public function removeFactory(string $type): void
    {
        unset($this->factories[$type]);
    }

    public function getFactory(string $type): ?array
    {
        return $this->factories[$type] ?? null;
    }

    public function setFactories(array $factories): void
    {
        $this->factories = [];

        foreach ($factories as $type => [$factory, $extensions]) {
            $this->addFactory($type, $factory, $extensions);
        }
    }

    public function getFactories(): array
    {
        return $this->factories;
    }

    /**
     * getSupportedExtensions
     *
     * @return  array
     */
    public function getSupportedExtensions(): array
    {
        return array_values(Arr::flatten(array_map(fn($factory) => $factory[1], $this->getFactories())));
    }

    /**
     * getFoundFileInfo
     *
     * @param  string  $filePath
     * @param  string  $path
     *
     * @return  array
     */
    protected function getFoundFileInfo(string $filePath, string $path): array
    {
        $filename = Str::removeLeft(
            $filePath = Path::normalize($filePath),
            $path = Path::normalize($path),
        );

        return [
            $filePath,
            $path,
            ltrim($filename, '/\\')
        ];
    }

    /**
     * alias
     *
     * @param  string  $alias
     * @param  string  $layout
     *
     * @return  static
     */
    public function alias(string $alias, string $layout): static
    {
        $this->aliases[$alias] = $layout;

        return $this;
    }

    public function resolveAlias(string $layout): string
    {
        while (isset($this->aliases[$layout])) {
            $layout = $this->aliases[$layout];
        }

        return $layout;
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @param  array  $aliases
     *
     * @return  static  Return self to support chaining.
     */
    public function setAliases(array $aliases): static
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * Extends engine after created, this is similar a decorator.
     *
     * @param  callable  $callable
     *
     * @return  static  Retrun self to support chaining.
     */
    public function extend(callable $callable): static
    {
        $builder = $this->getObjectBuilder();

        $handler = $builder->getBuilder();

        $handler = static function (array $options) use ($callable, $handler) {
            return $callable($handler($options), $options);
        };

        $builder->setBuilder($handler);

        return $this;
    }
}
