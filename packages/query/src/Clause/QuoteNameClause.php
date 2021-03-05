<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Query\Clause;

use Windwalker\Query\Query;

/**
 * The QuoteNameClause class.
 */
class QuoteNameClause implements ClauseInterface
{
    /**
     * QuoteNameClause constructor.
     *
     * @param  mixed       $value
     * @param  Query|null  $query
     */
    public function __construct(
        protected mixed $value,
        protected ?Query $query = null
    ) {
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param  mixed  $value
     *
     * @return  static  Return self to support chaining.
     */
    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return Query|null
     */
    public function getQuery(): ?Query
    {
        return $this->query;
    }

    /**
     * @param  Query|null  $query
     *
     * @return  static  Return self to support chaining.
     */
    public function setQuery(?Query $query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        $value = (string) $this->value;

        if ($this->query) {
            $value = $this->query->qnStr($value);
        }

        return $value;
    }
}
