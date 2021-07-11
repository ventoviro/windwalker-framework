<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Component;

use Windwalker\Edge\Edge;
use Windwalker\Edge\Extension\EdgeExtensionInterface;
use Windwalker\Edge\Extension\ParsersExtensionInterface;

/**
 * The ComponentExtension class.
 */
class ComponentExtension implements EdgeExtensionInterface, ParsersExtensionInterface
{
    /**
     * ComponentExtension constructor.
     *
     * @param  Edge   $edge
     * @param  array  $components
     */
    public function __construct(protected Edge $edge, protected array $components = [])
    {
        $this->registerComponent('dynamic-component', DynamicComponent::class);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'edge-component';
    }

    /**
     * @inheritDoc
     */
    public function getParsers(): array
    {
        return [
            [$this, 'parseComponents'],
        ];
    }

    public function parseComponents(string $content): string
    {
        $compiler = new ComponentTagCompiler($this->edge, $this->components);

        $content = $compiler->compile($content);

        return $content;
    }

    /**
     * @return array
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * @param  array  $components
     *
     * @return  static  Return self to support chaining.
     */
    public function setComponents(array $components): static
    {
        $this->components = $components;

        return $this;
    }

    public function registerComponent(string $name, string $class): static
    {
        $this->components[$name] = $class;

        return $this;
    }
}
