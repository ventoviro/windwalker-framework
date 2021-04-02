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
use Windwalker\Form\FormNormalizer;

/**
 * Trait ManageHTMLTrait
 */
trait ManageInputTrait
{
    public DOMElement $input;

    /**
     * getInput
     *
     * @param  array  $options
     *
     * @return  string
     */
    public function renderInput(array $options = []): string
    {
        $input = $this->getPreparedInput();

        return $this->getForm()->getRenderer()->renderInput($this, $input, $options);
    }

    public function buildInput(DOMElement $input, array $options = []): string|DOMElement
    {
        return $input;
    }

    public function getPreparedInput(): DOMElement
    {
        $input = clone $this->getInput();

        $input->setAttribute('id', $this->getId());
        $input->setAttribute('name', $this->getInputName());
        $input->setAttribute('data-field-input', true);

        $input = $this->prepareInput($input);

        FormNormalizer::sortAttributes($input);

        return $input;
    }

    /**
     * prepareRenderInput
     *
     * @param  DOMElement  $input
     *
     * @return  DOMElement
     */
    abstract public function prepareInput(DOMElement $input): DOMElement;

    /**
     * @return DOMElement
     */
    public function getInput(): DOMElement
    {
        return $this->input;
    }

    /**
     * @param  DOMElement  $element
     *
     * @return  static  Return self to support chaining.
     */
    public function setInput(DOMElement $element): static
    {
        $this->input = $element;

        return $this;
    }

    /**
     * getAttribute
     *
     * @param  string  $name
     *
     * @return string|null
     */
    public function getAttribute(string $name): ?string
    {
        $input = $this->getInput();

        if (!$input->hasAttribute($name)) {
            return null;
        }

        return $this->getInput()->getAttribute($name);
    }

    public function getAttributes(): array
    {
        return $this->input->getAttributes(true);
    }

    /**
     * getAttribute
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  static
     */
    public function setAttribute(string $name, mixed $value): static
    {
        $this->getInput()->setAttribute($name, $value);

        return $this;
    }

    /**
     * attr
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  static
     */
    public function attr(string $name, $value = null): static
    {
        return $this->setAttribute($name, $value);
    }

    public function getClass(): string
    {
        return $this->getInput()->classList->value;
    }

    public function setClass(string $class): static
    {
        $this->getInput()->setAttribute('class', $class);

        return $this;
    }

    public function addClass(...$args): static
    {
        $this->getInput()->classList->add(...$args);

        return $this;
    }

    public function removeClass(...$args): static
    {
        $this->getInput()->classList->remove(...$args);

        return $this;
    }
}
