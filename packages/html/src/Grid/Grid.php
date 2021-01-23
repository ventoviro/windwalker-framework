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
 * The Grid class to dynamically generate HTML tables.
 *
 * This is currently a Joomla JGrid fork.
 *
 * @see    https://github.com/joomla/joomla-cms/blob/staging/libraries/joomla/grid/grid.php
 *
 * @since  2.1
 */
class Grid
{
    public const ROW_HEAD = 1;

    public const ROW_FOOT = 2;

    public const ROW_NORMAL = 3;

    /**
     * Array of columns
     * @var array
     */
    protected array $columns = [];

    /**
     * Current active row
     * @var int
     */
    protected int $activeRow = 0;

    /**
     * Rows of the table (including header and footer rows)
     * @var array
     */
    protected array $rows = [];

    /**
     * Header and Footer row-IDs
     * @var array
     */
    protected array $specialRows = ['header' => [], 'footer' => []];

    /**
     * Associative array of attributes for the table-tag
     * @var array
     */
    protected array $attrs;

    /**
     * Constructor for a Grid object
     *
     * @param   array  $attrs  Associative array of attributes for the table-tag
     *
     * @since   2.1
     */
    public function __construct(array $attrs = [])
    {
        $this->setTableAttributes($attrs, true);
    }

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
     * Magic function to render this object as a table.
     *
     * @return  string
     *
     * @since 2.1
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Method to set the attributes for a table-tag
     *
     * @param   array  $attrs    Associative array of attributes for the table-tag
     * @param   bool   $replace  Replace possibly existing attributes
     *
     * @return  static This object for chaining
     *
     * @since 2.1
     */
    public function setTableAttributes(array $attrs = [], bool $replace = false): static
    {
        if ($replace) {
            $this->attrs = $attrs;
        } else {
            $this->attrs = array_merge($this->attrs, $attrs);
        }

        return $this;
    }

    /**
     * Get the Attributes of the current table
     *
     * @return  array Associative array of attributes
     *
     * @since 2.1
     */
    public function getTableAttributes(): array
    {
        return $this->attrs;
    }

    /**
     * Add new column name to process
     *
     * @param   string $name Internal column name
     *
     * @return  static This object for chaining
     *
     * @since 2.1
     */
    public function addColumn(string $name): static
    {
        $this->columns[] = $name;

        return $this;
    }

    /**
     * Returns the list of internal columns
     *
     * @return  array List of internal columns
     *
     * @since 2.1
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Delete column by name
     *
     * @param   string $name Name of the column to be deleted
     *
     * @return  static This object for chaining
     *
     * @since 2.1
     */
    public function deleteColumn(string $name): static
    {
        $index = array_search($name, $this->columns, true);

        if ($index !== false) {
            unset($this->columns[$index]);
            $this->columns = array_values($this->columns);
        }

        return $this;
    }

    /**
     * Method to set a whole range of columns at once
     * This can be used to re-order the columns, too
     *
     * @param   array $columns List of internal column names
     *
     * @return  static This object for chaining
     *
     * @since 2.1
     */
    public function setColumns(array $columns): static
    {
        $this->columns = array_values($columns);

        return $this;
    }

    /**
     * Adds a row to the table and sets the currently
     * active row to the new row
     *
     * @param   array     $attrs    Associative array of attributes for the row
     * @param   int|bool  $special  1 for a new row in the header, 2 for a new row in the footer
     *
     * @return  static This object for chaining
     *
     * @since   2.1
     */
    public function addRow(array $attrs = [], int|bool $special = self::ROW_NORMAL): static
    {
        $this->rows[]['_row'] = $attrs;
        $this->activeRow = count($this->rows) - 1;

        if ($special) {
            if ($special === static::ROW_HEAD) {
                $this->specialRows['header'][] = $this->activeRow;
            } elseif ($special === static::ROW_FOOT) {
                $this->specialRows['footer'][] = $this->activeRow;
            }
        }

        return $this;
    }

    /**
     * Method to get the attributes of the currently active row
     *
     * @return array Associative array of attributes
     *
     * @since   2.1
     */
    public function getRowAttributes(): array
    {
        return $this->rows[$this->activeRow]['_row'];
    }

    /**
     * Method to set the attributes of the currently active row
     *
     * @param   array  $attrs  Associative array of attributes
     *
     * @return  static This object for chaining
     *
     * @since   2.1
     */
    public function setRowAttributes(array $attrs): static
    {
        $this->rows[$this->activeRow]['_row'] = $attrs;

        return $this;
    }

    /**
     * Get the currently active row ID
     *
     * @return  int ID of the currently active row
     *
     * @since   2.1
     */
    public function getActiveRow(): int
    {
        return $this->activeRow;
    }

    /**
     * Set the currently active row
     *
     * @param   int $id ID of the row to be set to current
     *
     * @return  static This object for chaining
     *
     * @since  2.1
     */
    public function setActiveRow(int $id): static
    {
        $this->activeRow = (int) $id;

        return $this;
    }

    /**
     * Set cell content for a specific column for the
     * currently active row
     *
     * @param   string  $name     Name of the column
     * @param   string  $content  Content for the cell
     * @param   array   $attrs    Associative array of attributes for the td-element
     * @param   bool    $replace  If false, the content is appended to the current content of the cell
     *
     * @return  static This object for chaining
     *
     * @since 2.1
     */
    public function setRowCell(string $name, string $content, array $attrs = [], bool $replace = true): static
    {
        if ($replace || !isset($this->rows[$this->activeRow][$name])) {
            $cell = new \stdClass();
            $cell->attribs = $attrs;
            $cell->content = $content;
            $this->rows[$this->activeRow][$name] = $cell;
        } else {
            $this->rows[$this->activeRow][$name]->content .= $content;
            $this->rows[$this->activeRow][$name]->attribs = $attrs;
        }

        return $this;
    }

    /**
     * Get all data for a row
     *
     * @param   ?int $id ID of the row to return
     *
     * @return  ?array Array of columns of a table row
     *
     * @since   2.1
     */
    public function getRow(?int $id = null): ?array
    {
        $id ??= $this->activeRow;

        return $this->rows[$id] ?? null;
    }

    /**
     * Get the IDs of all rows in the table
     *
     * @param   int|bool $special false for the standard rows, 1 for the header rows, 2 for the footer rows
     *
     * @return  array Array of IDs
     *
     * @since 2.1
     */
    public function getRows(?int $special = null): array
    {
        if ($special) {
            if ($special === 1) {
                return $this->specialRows['header'];
            } else {
                return $this->specialRows['footer'];
            }
        }

        return array_diff(
            array_keys($this->rows),
            array_merge($this->specialRows['header'], $this->specialRows['footer'])
        );
    }

    /**
     * Delete a row from the object
     *
     * @param   int $id ID of the row to be deleted
     *
     * @return  static This object for chaining
     *
     * @since   2.1
     */
    public function deleteRow(int $id): static
    {
        unset($this->rows[$id]);

        if (in_array($id, $this->specialRows['header'], true)) {
            unset($this->specialRows['header'][array_search($id, $this->specialRows['header'])]);
        }

        if (in_array($id, $this->specialRows['footer'], true)) {
            unset($this->specialRows['footer'][array_search($id, $this->specialRows['footer'])]);
        }

        if ($this->activeRow === $id) {
            $this->activeRow = (int) array_key_last($this->rows);
        }

        return $this;
    }

    /**
     * Render the HTML table
     *
     * @return  string The rendered HTML table
     *
     * @since   2.1
     */
    public function toString(): string
    {
        $output = [];
        $output[] = '<table' . $this->renderAttributes($this->getTableAttributes()) . '>';

        if (count($this->specialRows['header'])) {
            $output[] = $this->renderArea($this->specialRows['header'], 'thead', 'th');
        }

        $ids = array_diff(
            array_keys($this->rows),
            array_merge($this->specialRows['header'], $this->specialRows['footer'])
        );

        if (count($ids)) {
            $output[] = $this->renderArea($ids);
        }

        if (count($this->specialRows['footer'])) {
            $output[] = $this->renderArea($this->specialRows['footer'], 'tfoot');
        }

        $output[] = '</table>';

        return implode('', $output);
    }

    /**
     * Render an area of the table
     *
     * @param   array  $ids  IDs of the rows to render
     * @param   string $area Name of the area to render. Valid: tbody, tfoot, thead
     * @param   string $cell Name of the cell to render. Valid: td, th
     *
     * @return string The rendered table area
     *
     * @since 2.1
     */
    protected function renderArea(array $ids, string $area = 'tbody', string $cell = 'td'): string
    {
        $output = [];
        $output[] = '<' . $area . ">\n";

        foreach ($ids as $id) {
            $output[] = "\t<tr" . $this->renderAttributes($this->rows[$id]['_row']) . ">\n";

            foreach ($this->getColumns() as $name) {
                if (isset($this->rows[$id][$name])) {
                    $column = $this->rows[$id][$name];
                    $output[] = "\t\t<" . $cell . $this->renderAttributes(
                        $column->attribs
                    ) . '>' . $column->content . '</' . $cell . ">\n";
                }
            }

            $output[] = "\t</tr>\n";
        }

        $output[] = '</' . $area . '>';

        return implode('', $output);
    }

    /**
     * Renders an HTML attribute from an associative array
     *
     * @param   array  $attrs  Associative array of attributes
     *
     * @return  string The HTML attribute string
     *
     * @since   2.1
     */
    protected function renderAttributes(array $attrs = []): string
    {
        if ($attrs === []) {
            return '';
        }

        $return = [];

        foreach ($attrs as $key => $value) {
            $return[] = $key . '="' . $value . '"';
        }

        return ' ' . implode(' ', $return);
    }
}
