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
     * @var string|Query|RawWrapper
     */
    protected mixed $value;

    /**
     * @var string|bool|null
     */
    protected mixed $alias;

    protected bool $isColumn = false;

    /**
     * @var Query
     */
    protected Query $query;

    /**
     * AsClause constructor.
     *
     * @param  Query  $query
     * @param  string|Query|null  $value
     * @param  string|bool|null  $alias
     * @param  bool  $isColumn
     */
    public function __construct(
        Query $query,
        mixed $value = null,
        string|bool|null $alias = null,
        bool $isColumn = true
    ) {
        $this->value = $value;
        $this->alias = $alias;
        $this->query = $query;
        $this->isColumn = $isColumn;
    }

    public function __toString(): string
    {
        $quoteMethod = $this->isColumn ? 'quoteName' : 'quote';
        $column = $this->value;
        $alias  = $this->alias;

        if ($column instanceof RawWrapper) {
            $column = $column();
        } elseif ($column instanceof Query) {
            $column = '(' . $column . ')';
        } elseif ($column instanceof Clause) {
            $column = $column->mapElements(fn($v) => $this->query->$quoteMethod(
                Query::convertClassToTable((string) $v)
            ));
        } else {
            $column = $this->query->$quoteMethod(
                Query::convertClassToTable((string) $column, $entityAlias)
            );

            $alias ??= $entityAlias;
        }

        if ($alias !== false && (string) $alias !== '') {
            $column .= ' AS ' . $this->query->quoteName($alias);
        }

        return (string) $column;
    }

    /**
     * Method to get property Alias
     *
     * @return string|null
     */
    public function getAlias(): mixed
    {
        return $this->alias;
    }

    /**
     * Method to set property alias
     *
     * @param  string|null  $alias
     *
     * @return  static  Return self to support chaining.
     */
    public function alias(mixed $alias): static
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
     * @param  string|Query  $column
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function value(mixed $column): static
    {
        $this->value = $column;

        return $this;
    }
}
