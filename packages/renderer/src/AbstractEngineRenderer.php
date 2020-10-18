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
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The AbstractEngineRenderer class.
 *
 * @since  2.0
 */
abstract class AbstractEngineRenderer implements
    RendererInterface,
    ExtendableRendererInterface,
    TemplateFactoryInterface
{
    use OptionAccessTrait;

    protected ?Closure $builder = null;

    /**
     * Property engine.
     *
     * @var object|null
     */
    protected object|null $engine = null;

    /**
     * AbstractEngineRenderer constructor.
     *
     * @param  array  $options
     */
    public function __construct(array $options = [])
    {
        $this->prepareOptions(
            [
                'cache_path' => null,
                'file_extensions' => null,
                'context' => null,
                'paths' => [],
                'root_path' => null,
                'debug' => null
            ],
            $options
        );
    }

    public function createEngine(array $options = []): object
    {
        $builder = $this->getBuilder();

        $options = Arr::mergeRecursive($this->options, $options);

        return $builder($options);
    }

    /**
     * @inheritDoc
     */
    public function render(string $layout, array $data = [], array $options = []): string
    {
        return $this->make($layout, $options)($data);
    }

    /**
     * Get default builder function.
     *
     * @return  Closure
     */
    abstract public function getDefaultBuilder(): Closure;

    /**
     * @inheritDoc
     */
    public function extend(callable $callable): static
    {
        $builder = $this->getBuilder();

        $callable = Closure::fromCallable($callable);

        $builder = static function (array $options) use ($callable, $builder) {
            return $callable($builder($options), $options);
        };

        $this->setBuilder($builder);

        return $this;
    }

    /**
     * @return Closure|null
     */
    public function getBuilder(): ?Closure
    {
        return $this->builder ?? $this->getDefaultBuilder();
    }

    /**
     * @param  Closure|null  $builder
     *
     * @return  static  Return self to support chaining.
     */
    public function setBuilder(?Closure $builder)
    {
        $this->builder = $builder;

        return $this;
    }
}
