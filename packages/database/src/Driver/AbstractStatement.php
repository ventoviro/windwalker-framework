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
use Windwalker\Database\Event\ItemFetchedEvent;
use Windwalker\Database\Event\QueryEndEvent;
use Windwalker\Database\Event\QueryFailedEvent;
use Windwalker\Database\Event\QueryStartEvent;
use Windwalker\Database\Exception\StatementException;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Query\Bounded\BindableTrait;

use function Windwalker\collect;
use function Windwalker\tap;

/**
 * The AbstractStatement class.
 */
abstract class AbstractStatement implements StatementInterface
{
    use BindableTrait;
    use EventAwareTrait;

    /**
     * @var mixed|resource
     */
    protected mixed $cursor = null;

    protected mixed $conn = null;

    /**
     * @var bool
     */
    protected bool $executed = false;

    /**
     * @var AbstractDriver
     */
    protected AbstractDriver $driver;

    /**
     * @var string
     */
    protected string $query;

    protected array $options = [];

    /**
     * AbstractStatement constructor.
     *
     * @param  AbstractDriver  $driver
     * @param  string           $query
     * @param  array            $bounded
     * @param  array            $options
     */
    public function __construct(AbstractDriver $driver, string $query, array $bounded = [], array $options = [])
    {
        $this->driver = $driver;
        $this->query = $query;
        $this->bounded = $bounded;
        $this->options = $options;
    }

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function getIterator(string|object $class = Collection::class, array $args = []): \Generator
    {
        $this->execute();

        while (($row = $this->fetch($class, $args)) !== null) {
            yield $row;
        }
    }

    /**
     * @inheritDoc
     */
    public function fetch(object|string $class = Collection::class, array $args = []): ?object
    {
        // todo: Implement more hydrators.
        $hydrator = $this->driver->getHydrator();

        $item = $this->doFetch();

        if ($item === null) {
            return null;
        }

        return $hydrator->hydrate(
            $item,
            is_string($class) ? new $class() : $class
        );
    }

    abstract protected function doFetch(array $args = []): ?array;

    /**
     * execute
     *
     * @param  array|null  $params
     *
     * @return  static
     * @throws \Throwable
     */
    public function execute(?array $params = null): static
    {
        if ($this->executed) {
            return $this;
        }

        $statement = $this;

        $this->emit(QueryStartEvent::class, compact('params'));

        try {
            $result = $this->doExecute($params);

            if (!$result) {
                throw new StatementException('Execute query statement failed.');
            }
        } catch (\RuntimeException $exception) {
            $statement->close();
            $event = $this->emit(QueryFailedEvent::class, compact('exception'));

            throw $event->getException();
        }

        $this->emit(QueryEndEvent::class, compact('result'));

        $this->executed = true;

        return $this;
    }

    /**
     * Execute query by driver.
     *
     * @param  array|null  $params
     *
     * @return  bool
     */
    abstract protected function doExecute(?array $params = null): bool;

    /**
     * @inheritDoc
     */
    public function get(string|object $class = Collection::class, array $args = []): ?object
    {
        return tap(
            $this->fetchedEvent($this->fetch($class, $args)),
            function () {
                $this->close();
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function all(string|object $class = Collection::class, array $args = []): Collection
    {
        $this->execute();

        $array = [];

        // Get all of the rows from the result set.
        while ($row = $this->fetch($class, $args)) {
            $array[] = $this->fetchedEvent($row);
        }

        $items = collect($array);

        $this->close();

        return $items;
    }

    /**
     * fetchedEvent
     *
     * @param  object|null  $item
     *
     * @return object|null
     */
    protected function fetchedEvent(?object $item): ?object
    {
        return $this->emit(ItemFetchedEvent::class, compact('item'))->getItem();
    }

    /**
     * @inheritDoc
     */
    public function loadColumn(int|string $offset = 0): Collection
    {
        return $this->all()
            ->mapProxy()
            ->values()
            ->column($offset);
    }

    /**
     * @inheritDoc
     */
    public function result(): ?string
    {
        $assoc = $this->get();

        if ($assoc === null) {
            return $assoc;
        }

        return $assoc->first();
    }

    /**
     * getInnerStatement
     *
     * @return  mixed|resource
     */
    public function getCursor(): mixed
    {
        return $this->cursor;
    }

    /**
     * isExecuted
     *
     * @return  bool
     */
    public function isExecuted(): bool
    {
        return $this->executed;
    }

    /**
     * @inheritDoc
     */
    public function addDispatcherDealer(EventDispatcherInterface $dispatcher): void
    {
        $this->getDispatcher()->registerDealer($dispatcher);
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        $this->close();
    }
}
