<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Component;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\StrNormalize;
use Windwalker\Utilities\TypeCast;

use function Windwalker\collect;
use function Windwalker\value;

/**
 * The ComponentAttributes class.
 */
class ComponentAttributes implements ArrayAccess, IteratorAggregate
{
    /**
     * The raw array of attributes.
     *
     * @var array
     */
    protected array $attributes = [];

    public static function wrap(array|self $attributes): static
    {
        if ($attributes instanceof static) {
            return $attributes;
        }

        return new static($attributes);
    }

    /**
     * Create a new component attribute bag instance.
     *
     * @param  array  $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the first attribute's value.
     *
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function first(mixed $default = null): mixed
    {
        return $this->getIterator()->current() ?? value($default);
    }

    /**
     * Get a given attribute from the attribute array.
     *
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? value($default);
    }

    /**
     * Determine if a given attribute exists in the attribute array.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Only include the given attribute from the attribute array.
     *
     * @param  mixed  $keys
     *
     * @return static
     */
    public function only(mixed $keys): static
    {
        if (is_null($keys)) {
            $values = $this->attributes;
        } else {
            $keys = (array) $keys;

            $values = Arr::only($this->attributes, $keys);
        }

        return new static($values);
    }

    /**
     * Exclude the given attribute from the attribute array.
     *
     * @param  mixed|array  $keys
     *
     * @return static
     */
    public function except(mixed $keys): static
    {
        if (is_null($keys)) {
            $values = $this->attributes;
        } else {
            $keys = (array) $keys;

            $values = Arr::except($this->attributes, $keys);
        }

        return new static($values);
    }

    /**
     * Filter the attributes, returning a bag of attributes that pass the filter.
     *
     * @param  callable  $callback
     *
     * @return static
     */
    public function filter(callable $callback): static
    {
        return new static(collect($this->attributes)->filter($callback)->all());
    }

    /**
     * Return a bag of attributes that have keys starting with the given value / pattern.
     *
     * @param  string  $string
     *
     * @return static
     */
    public function whereStartsWith(string $string): static
    {
        return $this->filter(
            function ($value, $key) use ($string) {
                return str_starts_with($key, $string);
            }
        );
    }

    /**
     * Return a bag of attributes with keys that do not start with the given value / pattern.
     *
     * @param  string  $string
     *
     * @return static
     */
    public function whereDoesntStartWith(string $string): static
    {
        return $this->filter(
            function ($value, $key) use ($string) {
                return !str_starts_with($key, $string);
            }
        );
    }

    /**
     * Return a bag of attributes that have keys starting with the given value / pattern.
     *
     * @param  string  $string
     *
     * @return static
     */
    public function thatStartWith(string $string): static
    {
        return $this->whereStartsWith($string);
    }

    /**
     * Exclude the given attribute from the attribute array.
     *
     * @param  mixed|array  $keys
     *
     * @return static
     */
    public function exceptProps(mixed $keys): static
    {
        $props = [];

        foreach ($keys as $key => $defaultValue) {
            $key = is_numeric($key) ? $defaultValue : $key;

            $props[] = $key;
            $props[] = StrNormalize::toKebabCase($key);
        }

        return $this->except($props);
    }

    /**
     * Conditionally merge classes into the attribute bag.
     *
     * @param  mixed|array  $classList
     *
     * @return static
     */
    public function class(mixed $classList): static
    {
        $classList = (array) $classList;

        $classes = [];

        foreach ($classList as $class => $constraint) {
            if (is_numeric($class)) {
                $classes[] = $constraint;
            } elseif ($constraint) {
                $classes[] = $class;
            }
        }

        return $this->merge(['class' => implode(' ', $classes)]);
    }

    /**
     * Merge additional attributes / values into the attribute bag.
     *
     * @param  array  $attributeDefaults
     * @param  bool   $escape
     *
     * @return static
     */
    public function merge(array $attributeDefaults = [], bool $escape = true)
    {
        $attributeDefaults = array_map(
            function ($value) use ($escape) {
                return $this->shouldEscapeAttributeValue($escape, $value)
                    ? e($value)
                    : $value;
            },
            $attributeDefaults
        );

        [$appendableAttributes, $nonAppendableAttributes] = collect($this->attributes)
            ->partition(
                function ($value, $key) use ($attributeDefaults) {
                    return $key === 'class' || $key === 'style' ||
                        (isset($attributeDefaults[$key]) &&
                            $attributeDefaults[$key] instanceof AppendableAttributeValue);
                },
                true
            );

        $attributes = $appendableAttributes->mapWithKeys(
            function ($value, $key) use ($attributeDefaults, $escape) {
                $defaultsValue = isset($attributeDefaults[$key])
                && $attributeDefaults[$key] instanceof AppendableAttributeValue
                    ? $this->resolveAppendableAttributeDefault($attributeDefaults, $key, $escape)
                    : ($attributeDefaults[$key] ?? '');

                return [$key => implode(' ', array_unique(array_filter([$defaultsValue, $value])))];
            }
        )->merge($nonAppendableAttributes)->dump();

        return new static(array_merge($attributeDefaults, $attributes));
    }

    /**
     * Determine if the specific attribute value should be escaped.
     *
     * @param  bool   $escape
     * @param  mixed  $value
     *
     * @return bool
     */
    protected function shouldEscapeAttributeValue($escape, $value)
    {
        if (!$escape) {
            return false;
        }

        return !is_object($value) &&
            !is_null($value) &&
            !is_bool($value);
    }

    /**
     * Create a new appendable attribute value.
     *
     * @param  mixed  $value
     *
     * @return AppendableAttributeValue
     */
    public function prepends(mixed $value): AppendableAttributeValue
    {
        return new AppendableAttributeValue($value);
    }

    /**
     * Resolve an appendable attribute value default value.
     *
     * @param  array   $attributeDefaults
     * @param  string  $key
     * @param  bool    $escape
     *
     * @return mixed
     */
    protected function resolveAppendableAttributeDefault(array $attributeDefaults, string $key, bool $escape): mixed
    {
        if ($this->shouldEscapeAttributeValue($escape, $value = $attributeDefaults[$key]->value)) {
            $value = e($value);
        }

        return $value;
    }

    /**
     * Get all of the raw attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set the underlying attributes.
     *
     * @param  array  $attributes
     *
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        if (
            isset($attributes['attributes']) &&
            (
                $attributes['attributes'] instanceof self || is_array($attributes['attributes'])
            )
        ) {
            $parentBag = static::wrap($attributes['attributes']);

            unset($attributes['attributes']);

            $attributes = $parentBag->merge($attributes, $escape = false)->getAttributes();
        }

        $this->attributes = $attributes;
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml(): string
    {
        return (string) $this;
    }

    /**
     * Merge additional attributes / values into the attribute bag.
     *
     * @param  array  $attributeDefaults
     *
     * @return string
     */
    public function __invoke(array $attributeDefaults = []): string
    {
        return (string) $this->merge($attributeDefaults);
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value at the given offset.
     *
     * @param  string  $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set the value at a given offset.
     *
     * @param  string  $offset
     * @param  mixed   $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * Remove the value at the given offset.
     *
     * @param  string  $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->attributes);
    }

    /**
     * Implode the attributes into a single HTML ready string.
     *
     * @return string
     */
    public function __toString(): string
    {
        $string = '';

        foreach ($this->attributes as $key => $value) {
            if ($value === false || is_null($value)) {
                continue;
            }

            $string .= ' ' . $key;

            if ($value !== true) {
                $value = TypeCast::toString($value, false);
                $string .= '="' . e(trim($value)) . '"';
            }
        }

        return trim($string);
    }
}
