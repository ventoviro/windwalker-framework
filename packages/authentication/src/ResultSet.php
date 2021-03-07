<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Authentication;

/**
 * The AuthResultSet class.
 */
class ResultSet implements \IteratorAggregate
{
    /**
     * @var AuthResult[]
     */
    protected array $results = [];

    public ?string $matchedMethod = null;

    /**
     * AuthResultSet constructor.
     *
     * @param  AuthResult[]  $results
     */
    public function __construct(array $results = [])
    {
        foreach ($results as $name => $result) {
            $this->addResult($name, $result);
        }
    }

    public function addResult(string $name, AuthResult $result): static
    {
        $this->results[$name] = $result;

        return $this;
    }

    public function isSuccess(): bool
    {
        foreach ($this->results as $result) {
            if ($result->isSuccess()) {
                return true;
            }
        }

        return false;
    }

    public function isFailure(): bool
    {
        return !$this->isSuccess();
    }

    /**
     * @return AuthResult[]
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @return string|null
     */
    public function getMatchedMethod(): ?string
    {
        return $this->matchedMethod;
    }

    public function getMatchedResult(): AuthResult
    {
        return $this->results[$this->getMatchedMethod()];
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Generator
    {
        foreach ($this->results as $k => $result) {
            yield $k => $result;
        }
    }
}
