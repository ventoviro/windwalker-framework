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
use Windwalker\Form\Field\Concern\{ManageFilterTrait,
    ManageInputTrait,
    ManageLabelTrait,
    ManageRenderTrait,
    ManageWrapperTrait};
use Windwalker\Form\Form;
use Windwalker\Form\FormFactory;
use Windwalker\Form\FormNormalizer;
use Windwalker\Utilities\Classes\FlowControlTrait;
use Windwalker\Utilities\Options\StateAccessTrait;
use Windwalker\Utilities\Str;

use function Windwalker\DOM\h;

/**
 * The AbstractField class.
 *
 * @method $this help(string $value)
 * @method mixed getHelp()
 * @method $this description(string $value)
 * @method mixed getDescription()
 * @method $this title(string $value)
 * @method mixed getTitle()
 * @method $this id(string $value)
 * @method $this required(bool $value)
 * @method mixed isRequired()
 * @method $this disabled(bool $value)
 * @method mixed isDisabled()
 * @method  $this  onchange(string $value = null)
 * @method  mixed  getOnchange()
 * @method  $this  onfocus(string $value = null)
 * @method  mixed  getOnfocus()
 * @method  $this  onblur(string $value = null)
 * @method  mixed  getOnblur()
 *
 * @since  2.0
 */
abstract class AbstractField
{
    use FlowControlTrait;
    use StateAccessTrait;
    use ManageFilterTrait;
    use ManageInputTrait;
    use ManageLabelTrait;
    use ManageWrapperTrait;
    use ManageRenderTrait;

    /**
     * Property name.
     *
     * @var  string
     */
    protected string $name = '';

    /**
     * Property fieldset.
     *
     * @var  ?string
     */
    protected string|null $fieldset = null;

    /**
     * Property control.
     *
     * @var  string
     */
    protected string $namespace = '';

    /**
     * Property value.
     *
     * @var  mixed
     */
    protected $value = null;

    /**
     * Property form.
     *
     * @var  Form
     */
    protected ?Form $form = null;

    /**
     * create
     *
     * @param  mixed  ...$args
     *
     * @return  static
     *
     * @since  3.5.19
     */
    public static function create(...$args): static
    {
        return new static(...$args);
    }

    /**
     * Constructor.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  array   $attributes
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $name = '', string $label = '', array $attributes = [])
    {
        $this->setName($name);

        $this->input = $this->createInputElement($attributes);
        $this->label = h('label', [], $label);
        $this->wrapper = h('div', [], '');
        $this->form = FormFactory::form();

        $this->resetValidators();
        $this->resetFilters();
        $this->resetViewFilters();

        $this->prepareState(
            [
                'no_label' => false,
                'vertical' => true,
            ]
        );
    }

    protected function createInputElement(array $attrs = []): DOMElement
    {
        return h('input', $attrs, null);
    }

    /**
     * renderView
     *
     * @return  string
     */
    public function renderView(): string
    {
        return (string) $this->getValue();
    }

    /**
     * render
     *
     * @param array $options
     *
     * @return string
     */
    public function render(array $options = []): string
    {
        $options = array_merge($this->getStates(), $options);

        $wrapper = $this->prepareWrapper(clone $this->getWrapper());

        return $this->getForm()->getRenderer()->renderField($this, $wrapper, $options);
    }

    /**
     * getId
     *
     * @param  string  $suffix
     *
     * @return  string
     */
    public function getId(string $suffix = ''): string
    {
        if ($id = $this->getAttribute('id')) {
            return $id . $suffix;
        }

        return $this->buildId($suffix);
    }

    public function buildId(string $suffix = ''): string
    {
        return 'input-' . FormNormalizer::clearAttribute($this->getNamespaceName(true)) . $suffix;
    }

    /**
     * prepareStore
     *
     * @param  mixed  $value
     *
     * @return  mixed
     */
    public function prepareStore(mixed $value): mixed
    {
        return $value;
    }

    /**
     * Method to get property Name
     *
     * @return  string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getNamespaceName(bool $withParent = false): string
    {
        $name = $this->name;
        $ns   = $this->getNamespace();

        if ($ns) {
            $name = $ns . '/' . $name;
        }

        if ($withParent && $ns = $this->getForm()->getNamespace()) {
            $name = $ns . '/' . $name;
        }

        return $name;
    }

    /**
     * Method to set property name
     *
     * @param  string  $name
     *
     * @return  static  Return self to support chaining.
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getInputName(string $suffix = ''): string
    {
        if ($this->getInput()->hasAttribute('name')) {
            return $this->getAttribute('name');
        }

        return $this->buildInputName(null, $suffix);
    }

    /**
     * buildFieldName
     *
     * @param  string|null  $name
     * @param  string       $suffix
     *
     * @return  string
     *
     * @since  3.5.14
     */
    public function buildInputName(?string $name = null, string $suffix = ''): string
    {
        $names = array_filter(
            [
                FormNormalizer::clearNamespace($this->getForm()->getNamespace()),
                FormNormalizer::clearNamespace($this->getNamespace()),
                FormNormalizer::clearNamespace($name ?? $this->getName())
            ],
            'strlen'
        );

        return static::buildName(implode('/', $names)) . $suffix;
    }

    /**
     * buildName
     *
     * @param  string|array  $names
     *
     * @return  string
     *
     * @since  3.5.14
     */
    public static function buildName(string|array $names): string
    {
        if (!is_array($names)) {
            $names = array_values(array_filter(explode('/', $names), 'strlen'));
        }

        $first = array_shift($names);

        $names = array_map(
            static function ($value) {
                return '[' . $value . ']';
            },
            $names
        );

        return $first . implode('', $names);
    }

    /**
     * Method to get property Fieldset
     *
     * @return  ?string
     */
    public function getFieldset(): ?string
    {
        return $this->fieldset;
    }

    /**
     * Method to set property fieldset
     *
     * @param   ?string  $fieldset
     *
     * @return  static  Return self to support chaining.
     */
    public function setFieldset(?string $fieldset): static
    {
        $this->fieldset = $fieldset;

        return $this;
    }

    /**
     * Method to get property Value
     *
     * @return  mixed
     */
    public function getValue(): mixed
    {
        return $this->viewFilter->filter($this->getComputedValue());
    }

    /**
     * getRawValue
     *
     * @return  mixed
     *
     * @since  3.5.21
     */
    public function getComputedValue(): mixed
    {
        return ($this->value !== null && $this->value !== '') ? $this->value : $this->get('default');
    }

    /**
     * getRawValue
     *
     * @return  mixed
     */
    public function getRawValue(): mixed
    {
        return $this->value;
    }

    /**
     * Method to set property value
     *
     * @param  null  $value
     *
     * @return  static  Return self to support chaining.
     */
    public function setValue($value): static
    {
        $this->value = $value;

        return $this;
    }

    public function bindValue(&$value): static
    {
        $this->value = &$value;

        return $this;
    }

    /**
     * Method to get property Control
     *
     * @return  string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Method to set property control
     *
     * @param  string  $namespace
     *
     * @return  static  Return self to support chaining.
     */
    public function setNamespace(string $namespace): static
    {
        $this->namespace = FormNormalizer::clearNamespace($namespace);

        return $this;
    }

    /**
     * appendNamespace
     *
     * @param  string  $ns
     *
     * @return  $this
     */
    public function appendNamespace(string $ns): static
    {
        $this->namespace .= '/' . $ns;

        $this->namespace = FormNormalizer::clearNamespace($this->namespace);

        return $this;
    }

    public function setDefaultValue($value): static
    {
        return $this->set('default', $value);
    }

    public function getDefaultValue()
    {
        return $this->get('default');
    }

    /**
     * Get attribute. Alias of `getAttribute()`.
     *
     * @param  string  $name     The attribute name.
     * @param  mixed   $default  The default value.
     *
     * @return mixed The return value of this attribute.
     */
    public function get(string $name, $default = null): mixed
    {
        return $this->getState($name, $default);
    }

    /**
     * set
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  static
     */
    public function set(string $name, mixed $value): static
    {
        $this->setState($name, $value);

        return $this;
    }

    /**
     * Method to get property Form
     *
     * @return  Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * Method to set property form
     *
     * @param  Form  $form
     *
     * @return  static  Return self to support chaining.
     */
    public function setForm(Form $form): static
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Escape html string.
     *
     * @param  string  $text
     *
     * @return  string
     *
     * @since  2.1.9
     */
    public function escape(?string $text): string
    {
        return htmlspecialchars((string) $text, ENT_COMPAT, 'UTF-8');
    }

    /**
     * getAccessors
     *
     * @return  array
     *
     * @since   3.1.2
     */
    protected function getAccessors(): array
    {
        return [
            'help',
            'description'
        ];
    }

    /**
     * __call
     *
     * @param  string  $method
     * @param  array   $args
     *
     * @return  mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call(string $method, array $args): mixed
    {
        $accessors = $this->getAccessors();

        if (isset($accessors[$method])) {
            $option = $accessors[$method];
        }

        if (in_array($method, $accessors, true)) {
            $option = $method;
        }

        if (isset($option)) {
            if (str_starts_with($method, 'get')) {
                return $this->get(strtolower(Str::removeLeft($method, 'get')));
            }

            if (str_starts_with($method, 'is')) {
                $v = $this->get(strtolower(Str::removeLeft($method, 'is')));

                return $v !== null && $v !== false;
            }

            return $this->set($method, $args[0]);
        }

        if (str_starts_with($method, 'get')) {
            return $this->getAttribute(strtolower(Str::removeLeft($method, 'get')));
        }

        if (str_starts_with($method, 'is')) {
            $v = $this->getAttribute(strtolower(Str::removeLeft($method, 'is')));

            return $v !== null && $v !== false;
        }

        return $this->setAttribute($method, $args[0]);

        // throw ExceptionFactory::badMethodCall($method, static::class);
    }

    /**
     * __toString
     *
     * @return  string
     *
     * @since  3.5.19
     */
    public function __toString(): string
    {
        return $this->render();
    }
}
