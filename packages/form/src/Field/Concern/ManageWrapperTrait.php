<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field\Concern;

use Windwalker\DOM\DOMElement;
use Windwalker\DOM\HTMLFactory;
use Windwalker\Form\FormNormalizer;

use function Windwalker\DOM\h;

/**
 * Trait ManageContainerTrait
 */
trait ManageWrapperTrait
{
    public DOMElement $wrapper;

    protected function prepareWrapper(DOMElement $wrapper): DOMElement
    {
        $wrapper->setAttribute('id', $this->getId('-wrapper'));
        $wrapper->setAttribute('data-field-wrapper', true);

        FormNormalizer::sortAttributes($wrapper);

        return $wrapper;
    }

    public function buildWrapper(DOMElement $wrapper, array $options = []): string
    {
        if (!$options['no_label']) {
            $wrapper->appendChild(
                HTMLFactory::parse($this->renderLabel($options))
            );
        }

        $wrapper->appendChild(
            h(
                'div',
                [],
                HTMLFactory::parse($this->renderInput($options))
            )
        );

        return $wrapper->render();
    }

    public function modifyWrapper(callable $handler): static
    {
        $handler($this->getWrapper());

        return $this;
    }

    /**
     * @return DOMElement
     */
    public function getWrapper(): DOMElement
    {
        return $this->wrapper;
    }

    /**
     * @param  DOMElement  $wrapper
     *
     * @return  static  Return self to support chaining.
     */
    public function setWrapper(DOMElement $wrapper): static
    {
        $this->wrapper = $wrapper;

        return $this;
    }

    /**
     * getAttribute
     *
     * @param  string  $name
     *
     * @return  string
     */
    public function getWrapperAttribute(string $name): string
    {
        return $this->getWrapper()->getAttribute($name);
    }

    /**
     * getAttribute
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  static
     */
    public function setWrapperAttribute(string $name, string $value): static
    {
        $this->getWrapper()->setAttribute($name, $value);

        return $this;
    }

    public function getWrapperAttributes(): array
    {
        return $this->getWrapper()->getAttributes(true);
    }

    /**
     * attr
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  static
     */
    public function wrapperAttr(string $name, $value = null): static
    {
        return $this->setAttribute($name, $value);
    }

    public function getWrapperClass(): string
    {
        return $this->getWrapper()->classList->value;
    }

    public function setWrapperClass(string $class): static
    {
        $this->getWrapper()->setAttribute('class', $class);

        return $this;
    }

    public function addWrapperClass(...$args): static
    {
        $this->getWrapper()->classList->add(...$args);

        return $this;
    }

    public function removeWrapperClass(...$args): static
    {
        $this->getWrapper()->classList->remove(...$args);

        return $this;
    }
}
