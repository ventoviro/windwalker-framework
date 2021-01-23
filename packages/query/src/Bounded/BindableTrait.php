<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Bounded;

use Windwalker\Query\Clause\ValueClause;

/**
 * The BoundedBag class.
 */
trait BindableTrait
{
    protected $bounded = [];

    /**
     * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query
     * execution. Also removes a variable that has been bounded from the internal bounded array when the passed in
     * value is null.
     *
     * @param  string|integer|array  $key            The key that will be used in your SQL query to reference the value.
     *                                               Usually of the form ':key', but can also be an integer.
     * @param  mixed                &$value          The value that will be bound. The value is passed by reference to
     *                                               support output parameters such as those possible with stored
     *                                               procedures.
     * @param  mixed                 $dataType       Constant corresponding to a SQL datatype.
     * @param  integer               $length         The length of the variable. Usually required for OUTPUT parameters.
     * @param  array                 $driverOptions  Optional driver options to be used.
     *
     * @return  static
     *
     * @since   3.5.5
     */
    public function bindParam(
        $key = null,
        &$value = null,
        $dataType = null,
        int $length = 0,
        $driverOptions = null
    ): static {
        // If is array, loop for all elements.
        if (is_array($key)) {
            foreach ($key as $k => &$v) {
                $this->bindParam($k, $v, $dataType, $length, $driverOptions);
            }

            return $this;
        }

        if ($dataType === null) {
            $dataType = ParamType::guessType(
                $value instanceof ValueClause ? $value->getValue() : $value
            );
        }

        $bounded = [
            'value' => &$value,
            'dataType' => $dataType,
            'length' => $length,
            'driverOptions' => $driverOptions,
        ];

        if ($key === null) {
            $this->bounded[] = $bounded;
        } else {
            $this->bounded[$key] = $bounded;
        }

        return $this;
    }

    /**
     * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query
     * execution. Also removes a variable that has been bounded from the internal bounded array when the passed in
     * value is null.
     *
     * @param  string|integer|array  $key            The key that will be used in your SQL query to reference the
     *                                               value. Usually of the form ':key', but can also be an integer.
     * @param  mixed                &$value          The value that will be bound. The value is passed by reference to
     *                                               support output parameters such as those possible with stored
     *                                               procedures.
     * @param  mixed                 $dataType       Constant corresponding to a SQL datatype.
     *
     * @return  static
     *
     * @since   2.0
     */
    public function bind(
        $key = null,
        $value = null,
        $dataType = null
    ): static {
        return $this->bindParam($key, $value, $dataType);
    }

    /**
     * Retrieves the bound parameters array when key is null and returns it by reference. If a key is provided then
     * that item is returned.
     *
     * @param  mixed  $key  The bounded variable key to retrieve.
     *
     * @return  array|null
     *
     * @since   2.0
     */
    public function &getBounded($key = null): ?array
    {
        if (empty($key)) {
            return $this->bounded;
        }

        return $this->bounded[$key] ?? null;
    }

    /**
     * resetBounded
     *
     * @return  static
     */
    public function resetBounded(): static
    {
        $this->bounded = [];

        return $this;
    }

    /**
     * unbind
     *
     * @param  string|array  $keys
     *
     * @return  static
     */
    public function unbind(mixed $keys): static
    {
        $keys = (array) $keys;

        $this->bounded = array_diff_key($this->bounded, array_flip($keys));

        return $this;
    }
}
