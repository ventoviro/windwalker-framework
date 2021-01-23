<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Iterator;

use Windwalker\Data\Collection;
use Windwalker\Database\Driver\AbstractStatement;

/**
 * Class DataIterator
 *
 * @since 2.0
 */
class StatementIterator implements \Countable, \Iterator
{
    /**
     * Property reader.
     *
     * @var  AbstractStatement
     */
    protected $stmt = null;

    /**
     * Property key.
     *
     * @var  int
     */
    protected $key = -1;

    /**
     * Property current.
     *
     * @var object
     */
    protected $current;

    /**
     * Property class.
     *
     * @var  string
     */
    protected $class;

    /**
     * @var array
     */
    protected $args;

    /**
     * Constructor.
     *
     * @param  AbstractStatement  $stmt
     * @param  string             $class
     * @param  array              $args
     */
    public function __construct(AbstractStatement $stmt, $class = Collection::class, array $args = [])
    {
        $this->stmt  = $stmt;
        $this->class = $class;
        $this->args  = $args;

        $this->next();
    }

    /**
     * Database iterator destructor.
     *
     * @since   2.0
     */
    public function __destruct()
    {
        $this->stmt->close();
    }

    /**
     * Return the current element
     *
     * @return mixed Can return any type.
     */
    public function current(): mixed
    {
        return $this->current;
    }

    /**
     * Move forward to next element
     *
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        // Try to get an object
        $this->current = $current = $this->stmt->fetch($this->args);

        if ($current) {
            $this->key++;
        }
    }

    /**
     * Return the key of the current element
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key(): mixed
    {
        return $this->key;
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return (bool) $this->current();
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @param  array|null  $params
     *
     * @return void Any returned value is ignored.
     */
    public function rewind(?array $params = null)
    {
        $this->stmt->close()->execute($params);
    }

    /**
     * Count elements of an object
     *
     * @return int The custom count as an integer.
     */
    public function count(): int
    {
        return $this->stmt->countAffected();
    }
}
