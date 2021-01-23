<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Clause;

use Windwalker\Query\Query;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * The AsClause class.
 */
class AsClause implements ClauseInterface
{
    /**
     * @var string|Query
     */
    protected $value;

    /**
     * @var string|bool|null
     */
    protected $alias;

    /**
     * AsClause constructor.
     *
     * @param  string|Query|RawWrapper  $value
     * @param  string|bool|null         $alias
     */
    public function __construct($value = null, $alias = null)
    {
        $this->value = $value;
        $this->alias = $alias;
    }

    public function __toString(): string
    {
        $column = $this->value;
        $alias = $this->alias;

        if ($column instanceof Query) {
            $column = '(' . $column . ')';
        }

        if ($alias !== false && (string) $alias !== '') {
            $column .= ' AS ' . $alias;
        }

        return (string) $column;
    }

    /**
     * Method to get property Alias
     *
     * @return  string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Method to set property alias
     *
     * @param  string  $alias
     *
     * @return  static  Return self to support chaining.
     */
    public function alias(string $alias): static
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Method to get property Column
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Method to set property column
     *
     * @param  string  $column
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function value(string $column): static
    {
        $this->value = $column;

        return $this;
    }
}
