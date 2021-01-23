<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The QueryFailedEvent class.
 */
class QueryFailedEvent extends AbstractEvent
{
    protected \Throwable $exception;
    protected string $sql;
    protected array $bounded;

    /**
     * @var mixed
     */
    protected $query;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     *
     * @return  static  Return self to support chaining.
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @param  string  $sql
     *
     * @return  static  Return self to support chaining.
     */
    public function setSql(string $sql): static
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * @return array
     */
    public function getBounded(): array
    {
        return $this->bounded;
    }

    /**
     * @param  array  $bounded
     *
     * @return  static  Return self to support chaining.
     */
    public function setBounded(array $bounded): static
    {
        $this->bounded = $bounded;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuery(): mixed
    {
        return $this->query;
    }

    /**
     * @param  mixed  $query
     *
     * @return  static  Return self to support chaining.
     */
    public function setQuery($query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return \Throwable
     */
    public function getException(): \Throwable
    {
        return $this->exception;
    }

    /**
     * @param  \Throwable  $exception
     *
     * @return  static  Return self to support chaining.
     */
    public function setException(\Throwable $exception): static
    {
        $this->exception = $exception;

        return $this;
    }
}
