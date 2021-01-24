<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\DOM\DOMElement;
use Windwalker\DOM\HTMLFactory;
use Windwalker\Filter\Rule\Length;

use function Windwalker\DOM\h;

/**
 * The TextField class.
 *
 * @method  $this  placeholder(string $value = null)
 * @method  mixed  getPlaceholder()
 * @method  $this  size(string $value = null)
 * @method  mixed  getSize()
 * @method  $this  autofocus(string $value = null)
 * @method  mixed  getAutofocus()
 * @method  $this  autocomplete(string $value = null)
 * @method  mixed  getAutocomplete()
 *
 * @since  2.0
 */
class TextField extends AbstractInputField
{
    protected string $inputType = 'text';

    /**
     * Property options.
     *
     * @var  DOMElement[]
     */
    protected array $options = [];

    public function buildInput(DOMElement $input, array $options = []): string
    {
        $html = parent::buildInput($input, $options);

        if (count($this->options)) {
            $html = h(
                'div',
                [],
                [
                    $html,
                    h(
                        'datalist',
                        [],
                        $this->options
                    ),
                ]
            );
        }

        return $html;
    }

    /**
     * @inheritDoc
     */
    public function prepareInput(DOMElement $input): DOMElement
    {
        $input['type']  = $this->getInputType();
        $input['value'] = $this->escape($this->getValue());
        $input['list']  = $this->getAttribute('list') ?? $this->buildListId();

        return $input;
    }

    public function buildListId(): ?string
    {
        return count($this->options) ? $this->getId() . '-list' : null;
    }

    /**
     * addOption
     *
     * @param  DOMElement  $option
     *
     * @return  static
     */
    public function addOption(DOMElement $option): static
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * option
     *
     * @param  string  $value
     * @param  array   $attrs
     *
     * @return static
     */
    public function option($value = null, array $attrs = []): static
    {
        $attrs['value'] = $value;

        $this->addOption(HTMLFactory::option($attrs));

        return $this;
    }

    /**
     * setOptions
     *
     * @param  array|Option[]  $options
     *
     * @return  static
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * max
     *
     * @param ?int   $length
     * @param  bool  $addFilter
     * @param  bool  $utf8
     *
     * @return  static|mixed
     *
     * @since  3.4.2
     */
    public function maxlength(?int $length = null, bool $addFilter = true, bool $utf8 = true): mixed
    {
        if ($addFilter) {
            $this->addFilter(new Length($length, $utf8));
        }

        return $this->setAttribute('maxlength', (string) $length);
    }
}
