<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Html\Grid;

/**
 * The KeyValueGrid class.
 *
 * @since  2.1.1
 */
class KeyValueGrid extends Grid
{
    public const COL_KEY = 'key';

    public const COL_VALUE = 'value';

    public const ROW = 'row';

    /**
     * create
     *
     * @param array  $attrs
     *
     * @return static
     */
    public static function create(array $attrs = []): static
    {
        return new static($attrs);
    }

    /**
     * Class init.
     *
     * @param array  $attrs
     */
    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);

        $this->setColumns([static::COL_KEY, static::COL_VALUE]);
    }

    /**
     * addHeader
     *
     * @param string  $keyTitle
     * @param string  $valueTitle
     * @param array   $attrs
     *
     * @return  static
     */
    public function addHeader(string $keyTitle = 'Key', string $valueTitle = 'Value', array $attrs = []): static
    {
        $this->addRow((array) $this->getValue($attrs, static::ROW), static::ROW_HEAD)
            ->setRowCell(static::COL_KEY, $keyTitle, (array) $this->getValue($attrs, static::COL_KEY))
            ->setRowCell(static::COL_VALUE, $valueTitle, (array) $this->getValue($attrs, static::COL_VALUE));

        return $this;
    }

    /**
     * addItem
     *
     * @param string  $key
     * @param string  $value
     * @param array   $attrs
     *
     * @return static
     */
    public function addItem(string $key, $value = null, array $attrs = []): static
    {
        if (is_array($value) || is_object($value)) {
            $value = print_r($value, true);
        }

        $this->addRow((array) $this->getValue($attrs, static::ROW))
            ->setRowCell(static::COL_KEY, $key, (array) $this->getValue($attrs, static::COL_KEY));

        if ($value !== false) {
            $this->setRowCell(static::COL_VALUE, $value, (array) $this->getValue($attrs, static::COL_VALUE));
        }

        return $this;
    }

    /**
     * addItems
     *
     * @param array  $items
     * @param array   $attrs
     *
     * @return  static
     */
    public function addItems(array $items = null, array $attrs = []): static
    {
        $this->configure(
            $items,
            function (KeyValueGrid $grid, $key, $value) use ($attrs) {
                $grid->addItem($key, $value, $attrs);
            }
        );

        return $this;
    }

    /**
     * addTitle
     *
     * @param string  $name
     * @param array   $attrs
     *
     * @return  static
     */
    public function addTitle(string $name, array $attrs = []): static
    {
        $attrs[static::COL_KEY]['colspan'] = 2;

        $this->addItem($name, false, $attrs);

        return $this;
    }

    /**
     * configureRows
     *
     * @param   array    $items
     * @param   callable $handler
     *
     * @return  static
     */
    public function configure(array $items, callable $handler): static
    {
        if (!is_callable($handler)) {
            throw new \InvalidArgumentException(__METHOD__ . ' Handler should be callable.');
        }

        if (!$items instanceof \Traversable && !is_array($items)) {
            throw new \InvalidArgumentException(__METHOD__ . ' items should be array or iterator.');
        }

        foreach ($items as $key => $item) {
            $handler($this, $key, $item);
        }

        return $this;
    }

    /**
     * getValue
     *
     * @param array  $options
     * @param string $name
     * @param mixed  $default
     *
     * @return  mixed
     */
    protected function getValue(array $options, string $name, $default = null): mixed
    {
        return $options[$name] ?? $default;
    }
}
