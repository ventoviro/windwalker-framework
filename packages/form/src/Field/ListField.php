<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field;

use DOMNode;
use MyCLabs\Enum\Enum;
use Windwalker\Data\Collection;
use Windwalker\DOM\DOMElement;
use Windwalker\DOM\HTMLFactory;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\TypeCast;

use function Windwalker\DOM\h;
use function Windwalker\value_compare;

/**
 * The ListField class.
 *
 * @method  $this  size(int $value)
 * @method  mixed  getSize()
 * @method  $this  multiple(bool $value = null)
 * @method  mixed  isMultiple()
 *
 * @since  2.0
 */
class ListField extends AbstractField
{
    /**
     * Property options.
     *
     * @var  DOMElement[]
     */
    protected array $options = [];

    /**
     * Property currentGroup.
     *
     * @var  string
     */
    protected ?string $currentGroup = null;

    /**
     * @inheritDoc
     */
    public function prepareInput(DOMElement $input): DOMElement
    {
        return $input;
    }

    protected function createInputElement(array $attrs = []): DOMElement
    {
        return h('select', $attrs, null);
    }

    /**
     * buildInput
     *
     * @param  DOMElement  $input
     * @param  array       $options
     *
     * @return DOMElement
     */
    public function buildFieldElement(DOMElement $input, array $options = []): string|DOMElement
    {
        foreach ($this->getOptions() as $key => $option) {
            $this->appendOption($input, $option, (string) $key);
        }

        if ($this->isMultiple()) {
            $input['name'] = $this->getInputName('[]');
        }

        return $input;
    }

    private function appendOption(DOMElement $select, DOMElement|array $option, ?string $group = null): void
    {
        if (is_array($option)) {
            $select->appendChild($optGroup = h('optgroup', ['label' => $group]));

            foreach ($option as $opt) {
                $this->appendOption($optGroup, $opt);
            }

            return;
        }

        $option = clone $option;
        $value = $this->getValue();

        if (!$this->isMultiple()) {
            if (value_compare($option['value'], $value, '==')) {
                $option->setAttribute('selected', 'selected');
            }
        } elseif (value_compare($option['value'], $value, 'in')) {
            $option->setAttribute('selected', 'selected');
        }

        $select->appendChild($option);
    }

    /**
     * getValue
     *
     * @return  array|string
     */
    public function getValue(): array|string
    {
        $value = parent::getValue();

        if ($this->isMultiple()) {
            if (is_array($value)) {
                return $value;
            }

            if (is_json($value)) {
                return json_decode($value, true);
            }

            if (is_string($value)) {
                return Collection::explode(',', (string) $value)
                    ->map('trim')
                    ->filter('strlen')
                    ->dump();
            }

            return TypeCast::toArray($value);
        } else {
            $value = (string) $value;
        }

        return $value;
    }

    /**
     * getOptions
     *
     * @return  array|DOMElement[]
     */
    public function getOptions(): array
    {
        return array_merge($this->options, $this->prepareOptions());
    }

    /**
     * setOptions
     *
     * @param  array|DOMElement[]  $options
     * @param  null|string         $group
     *
     * @return  static
     */
    public function setOptions(array $options, ?string $group = null): static
    {
        $this->resetOptions();
        $this->addOptions($options, $group);

        return $this;
    }

    /**
     * addOptions
     *
     * @param  array|DOMElement[]  $options
     * @param  null|string         $group
     *
     * @return  $this
     *
     * @since  3.5.19
     */
    public function addOptions(array $options, ?string $group = null): static
    {
        if ($group) {
            $options = [$group => $options];
        }

        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * resetOptions
     *
     * @return  $this
     *
     * @since  3.5.19
     */
    public function resetOptions(): static
    {
        $this->options = [];

        return $this;
    }

    /**
     * register
     *
     * @param  callable  $handler
     *
     * @return  static
     *
     * @since  3.5.2
     */
    public function register(callable $handler): self
    {
        $handler($this);

        return $this;
    }

    /**
     * registerOptions
     *
     * @param  iterable|string  $options
     * @param  callable|null    $handler
     *
     * @return  static
     *
     * @since  3.5.2
     */
    public function registerOptions(iterable|string $options, ?callable $handler = null): self
    {
        $isEnum = false;

        if (is_string($options)) {
            if ($isEnum = is_subclass_of($options, Enum::class)) {
                $options = array_flip($options::toArray());
            } else {
                TypeAssert::assert(
                    false,
                    'Options class must be Enum, {value} given.',
                    $options
                );
            }
        }

        $isList = array_is_list($options);

        foreach ($options as $name => $option) {
            if ($isList && !$isEnum) {
                // Option
                if ($handler) {
                    $handler($this, $option, null);
                } else {
                    $this->option((string) $option, (string) $option);
                }
            } elseif (is_array($option)) {
                foreach ($option as $opt) {
                    // Group
                    if ($handler) {
                        $handler($this, $opt, null, $name);
                    } else {
                        $this->option((string) $opt, (string) $opt, [], (string) $name);
                    }
                }
            } else {
                // Option
                if ($handler) {
                    $handler($this, $option, $name);
                } else {
                    $this->option((string) $option, (string) $name);
                }
            }
        }

        return $this;
    }

    /**
     * addOption
     *
     * @param  DOMElement   $option
     * @param  string|null  $group
     *
     * @return  static
     */
    public function addOption(DOMElement $option, ?string $group = null): static
    {
        $options = [$option];

        if ($group === null) {
            $group = $this->currentGroup;
        }

        $this->addOptions($options, $group);

        return $this;
    }

    /**
     * option
     *
     * @param  string|null  $text
     * @param  string|null  $value
     * @param  array        $attrs
     * @param  string|null  $group
     *
     * @return static
     */
    public function option(
        DOMNode|string|null $text = null,
        ?string $value = null,
        array $attrs = [],
        ?string $group = null
    ): static {
        $attrs['value'] = $value;

        $this->addOption(static::createOption($text, $value, $attrs), $group);

        return $this;
    }

    public static function createOption(
        DOMNode|string|null $text = null,
        ?string $value = null,
        array $attrs = []
    ): DOMElement {
        $attrs['value'] = $value;

        return HTMLFactory::option($attrs, $text);
    }

    /**
     * optionGroup
     *
     * @param  string    $name
     * @param  callable  $callback
     *
     * @return  static
     */
    public function group(string $name, callable $callback): static
    {
        $this->currentGroup = $name;

        $callback($this);

        $this->currentGroup = null;

        return $this;
    }

    /**
     * prepareOptions
     *
     * @return  array<DOMElement|mixed>
     */
    protected function prepareOptions(): array
    {
        return [];
    }
}
