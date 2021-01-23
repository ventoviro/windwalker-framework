<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver;

use Psr\EventDispatcher\EventDispatcherInterface;
use Windwalker\Data\Collection;
use Windwalker\Event\EventListenableInterface;
use Windwalker\Query\Bounded\BindableInterface;

/**
 * Interface StatementInterface
 */
interface StatementInterface extends BindableInterface, \IteratorAggregate, EventListenableInterface
{
    /**
     * execute
     *
     * @param  array|null  $params
     *
     * @return  static
     */
    public function execute(?array $params = null): static;

    /**
     * Fetch 1 row and move cursor to next position.
     *
     * @param  array  $args
     *
     * @return  Collection|null
     */
    public function fetch(array $args = []): ?Collection;

    /**
     * Fetch 1 row and close ths cursor.
     *
     * @param  array  $args
     *
     * @return  Collection|null
     */
    public function get(array $args = []): ?Collection;

    /**
     * Fetch all items and close cursor.
     *
     * @param  array  $args
     *
     * @return Collection
     */
    public function all(array $args = []): Collection;

    /**
     * Fetch all column values and close the cursor.
     *
     * @param  int|string  $offset
     *
     * @return  Collection
     */
    public function loadColumn(int|string $offset = 0): Collection;

    /**
     * Fetch first cell and close the cursor.
     *
     * @return  string|null
     */
    public function result(): ?string;

    /**
     * Close cursor and free result.
     *
     * @return  static
     */
    public function close(): static;

    /**
     * Count results.
     *
     * @return  int
     */
    public function countAffected(): int;

    /**
     * Get current cursor.
     *
     * @return  mixed
     */
    public function getCursor(): mixed;

    /**
     * isExecuted
     *
     * @return  bool
     */
    public function isExecuted(): bool;

    /**
     * getIterator
     *
     * @param  string  $class
     * @param  array   $args
     *
     * @return  \Generator
     */
    public function getIterator($class = Collection::class, array $args = []): \Generator;

    /**
     * addDispatcherDealer
     *
     * @param  EventDispatcherInterface  $dispatcher
     *
     * @return  void
     */
    public function addDispatcherDealer(EventDispatcherInterface $dispatcher): void;
}
