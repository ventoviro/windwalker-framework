<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form;

use Generator;

/**
 * The ResultSet class.
 */
class ResultSet
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
        foreach ($this->results as $result) {
            if ($result->isFailure()) {
                return false;
            }
        }

        return true;
    }

    public function isFailure(): bool
    {
        return !$this->isSuccess();
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
}
