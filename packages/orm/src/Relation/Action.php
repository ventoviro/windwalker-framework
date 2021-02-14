<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation;

/**
 * The Action class.
 */
class Action
{
    /**
     * Delete or update the row from the parent table, and automatically delete or update the matching rows
     * in the child table.
     *
     * @const  string
     */
    public const CASCADE   = 'CASCADE';

    /**
     * Rejects the delete or update operation for the parent table.
     *
     * @const  string
     */
    public const NO_ACTION = 'NO ACTION';

    /**
     * Rejects the delete or update operation for the parent table.
     *
     * Same as NO_ACTION.
     *
     * @const  string
     */
    public const RESTRICT  = 'NO ACTION';

    /**
     * Delete or update the row from the parent table, and set the foreign key column or columns in the child table to NULL.
     *
     * @const  string
     */
    public const SET_NULL  = 'SET NULL';
}
