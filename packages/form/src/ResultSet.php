<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form;

use Countable;
use Generator;
use IteratorAggregate;

/**
 * The ResultSet class.
 */
class ResultSet implements IteratorAggregate, Countable
{
    /**
     * @var ValidateResult[]
     */
    protected array $results = [];

    /**
     * AuthResultSet constructor.
     *
     * @param  ValidateResult[]  $results
     */
    public function __construct(array $results = [])
    {
        foreach ($results as $name => $result) {
            $this->addResult($name, $result);
        }
    }

    public function addResult(string $name, ValidateResult $result): static
    {
        $this->results[$name] = $result;

        return $this;
    }

    public function getResult(string $name): ?ValidateResult
    {
        return $this->results[$name] ?? null;
    }

    public function isSuccess(): bool
    {
        return $this->getFirstFailure() === null;
    }

    public function isFailure(): bool
    {
        return !$this->isSuccess();
    }

    public function first(): ?ValidateResult
    {
        return $this->results[array_key_first($this->results)] ?? null;
    }

    public function getFirstFailure(): ?ValidateResult
    {
        foreach ($this->results as $result) {
            if ($result->isFailure()) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @return ValidateResult[]
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Generator
    {
        foreach ($this->results as $k => $result) {
            yield $k => $result;
        }
    }

    public function count(): int
    {
        return count($this->results);
    }
}
