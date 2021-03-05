<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Clause;

use Windwalker\Utilities\Classes\FlowControlTrait;

/**
 * Query Clause Class.
 *
 * @property-read  string $name      The name of the element.
 * @property-read  array  $elements  An array of elements.
 * @property-read  string $glue      Glue piece.
 *
 * @since  2.0
 */
class Clause implements \Countable, ClauseInterface
{
    use FlowControlTrait;

    /**
     * @var    string  The name of the element.
     * @since  2.0
     */
    public string $name = '';

    /**
     * @var    array  An array of elements.
     * @since  2.0
     */
    public array $elements = [];

    /**
     * @var    string  Glue piece.
     * @since  2.0
     */
    public string $glue = ' ';

    /**
     * Constructor.
     *
     * @param   string $name     The name of the clause.
     * @param   mixed  $elements String or array.
     * @param   string $glue     The glue for elements.
     *
     * @since   2.0
     */
    public function __construct(string $name = '', $elements = [], string $glue = ' ')
    {
        $this->name = $name;
        $this->glue = $glue;

        if ($elements !== null) {
            $this->append($elements);
        }
    }

    /**
     * Magic function to convert the query element to a string.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * toString
     *
     * @return  string
     */
    public function render(): string
    {
        $elements = array_filter(
            $this->elements,
            fn ($arg) => $arg !== '' && $arg !== null && $arg !== false,
        );

        if (str_ends_with($this->name, '()')) {
            return substr($this->name, 0, -2) . '(' . implode($this->glue, $elements) . ')';
        }

        $sep = str_ends_with($this->name, "\n") ? '' : ' ';

        return trim($this->name . $sep . implode($this->glue, $elements));
    }

    /**
     * Appends element parts to the internal list.
     *
     * @param   mixed $elements String or array.
     *
     * @return  static
     *
     * @since   2.0
     */
    public function append(mixed $elements): static
    {
        if (is_array($elements)) {
            $this->elements = array_merge($this->elements, $elements);
        } else {
            $this->elements = array_merge($this->elements, [$elements]);
        }

        return $this;
    }

    /**
     * prepend
     *
     * @param   mixed $elements String or array.
     *
     * @return  static
     */
    public function prepend(mixed $elements): static
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }

        array_unshift($this->elements, ...$elements);

        return $this;
    }

    /**
     * each
     *
     * @param  callable  $callable
     *
     * @return  static
     */
    public function mapElements(callable $callable): static
    {
        $new = clone $this;
        $new->elements = array_map($callable, $this->elements);

        return $new;
    }

    /**
     * each
     *
     * @param  callable  $callable
     *
     * @return  static
     */
    public function filterElements(callable $callable): static
    {
        $new = clone $this;
        $new->elements = array_filter($this->elements, $callable);

        return $new;
    }

    /**
     * Gets the elements of this element.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * Method to provide deep copy support to nested objects and arrays
     * when cloning.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function __clone()
    {
        foreach (get_object_vars($this) as $k => $v) {
            if (is_object($v)) {
                $this->$k = clone $v;
            }
        }

        foreach ($this->elements as $k => $element) {
            if (is_object($element) && !$element instanceof ValueClause) {
                $this->elements[$k] = clone $element;
            }
        }
    }

    /**
     * Method to get property Glue
     *
     * @return  string
     */
    public function getGlue(): string
    {
        return $this->glue;
    }

    /**
     * Method to set property glue
     *
     * @param   string $glue
     *
     * @return  static  Return self to support chaining.
     */
    public function setGlue(string $glue): static
    {
        $this->glue = $glue;

        return $this;
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

    /**
     * Method to set property name
     *
     * @param   string $name
     *
     * @return  static  Return self to support chaining.
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * Method to set property elements
     *
     * @param  array|string  $elements
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setElements(mixed $elements): static
    {
        $this->elements = [];

        $this->append($elements);

        return $this;
    }
}
