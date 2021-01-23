<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Bounded;

/**
 * Interface BindableInterface
 */
interface BindableInterface
{
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
     */
    public function bindParam(
        $key = null,
        &$value = null,
        $dataType = null,
        int $length = 0,
        $driverOptions = null
    ): static;

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
     */
    public function bind(
        $key = null,
        $value = null,
        $dataType = null
    ): static;
}
