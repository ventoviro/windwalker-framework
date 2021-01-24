<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query;

use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Utilities\Str;

use function Windwalker\value;

/**
 * The Escaper class.
 */
class Escaper
{
    /**
     * @var \PDO|callable|mixed
     */
    protected $connection;

    /**
     * @var Query
     */
    protected $query;

    /**
     * Escaper constructor.
     *
     * @param  \PDO|callable|mixed  $connection
     * @param  Query|null           $query
     */
    public function __construct(mixed $connection, ?Query $query = null)
    {
        $this->connection = $connection;
        $this->query      = $query;
    }

    public function escape(string $value): string
    {
        return static::tryEscape($this->getConnection(), $value);
    }

    public function quote(string $value): string
    {
        return static::tryQuote($this->getConnection(), $value);
    }

    public function quoteMultiple($value): array|string
    {
        return $this->getDriver()->quote((string) $value);
    }

    /**
     * escape
     *
     * @param  \PDO|callable|mixed  $escaper
     * @param  int|string           $value
     *
     * @return  string
     */
    public static function tryEscape(mixed $escaper, string|int $value): string
    {
        if (is_callable($escaper)) {
            return $escaper($value, [static::class, 'stripQuote']);
        }

        if ($escaper instanceof \PDO) {
            return static::stripQuote((string) $escaper->quote((string) $value));
        }

        if ($escaper instanceof AbstractDriver) {
            return $escaper->escape((string) $value);
        }

        return $escaper->escape($value);
    }

    /**
     * quote
     *
     * @param  \PDO|callable|mixed  $escaper
     * @param  int|string           $value
     *
     * @return  string
     */
    public static function tryQuote(mixed $escaper, string|int $value): string
    {
        // PDO has quote method, directly use it.
        if ($escaper instanceof \PDO) {
            return (string) $escaper->quote((string) $value);
        }

        if ($escaper instanceof DatabaseAdapter) {
            return $escaper->getDriver()->quote((string) $value);
        }

        return "'" . static::tryEscape($escaper, $value) . "'";
    }

    /**
     * stripQuote
     *
     * @param  string  $value
     *
     * @return  string
     */
    public static function stripQuote(string $value): string
    {
        return substr(
            substr(
                (string) $value,
                0,
                -1
            ),
            1
        );
    }

    /**
     * stripQuoteIfExists
     *
     * @param  string|null  $value
     * @param  string  $sign
     *
     * @return  string
     */
    public static function stripQuoteIfExists(?string $value, string $sign = "'"): ?string
    {
        if ($value === null) {
            return $value;
        }

        if (Str::startsWith($value, $sign) && Str::endsWith($value, $sign)) {
            return static::stripQuote($value);
        }

        return $value;
    }

    /**
     * Method to get property Connection
     *
     * @return  mixed
     */
    public function getConnection(): mixed
    {
        if ($this->connection instanceof \WeakReference) {
            $conn = $this->connection->get();
        } else {
            $conn = $this->connection;
        }

        return $conn ?: [$this->query->getGrammar(), 'localEscape'];
    }

    /**
     * Method to set property connection
     *
     * @param  mixed  $connection
     *
     * @return  static  Return self to support chaining.
     */
    public function setConnection(mixed $connection): static
    {
        if ($connection instanceof DatabaseAdapter) {
            $connection = $connection->getDriver();
        }

        $this->connection = $connection;

        return $this;
    }
}
