<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter;

use Windwalker\Filter\Exception\ValidateException;
use Windwalker\Utilities\Assert\Assert;
use Windwalker\Utilities\Iterator\PriorityQueue;
use Windwalker\Utilities\TypeCast;

/**
 * The ChainFilter class.
 */
class ChainFilter implements FilterInterface, ValidatorInterface
{
    /**
     * @var FilterInterface[]|ValidatorInterface[]|PriorityQueue
     */
    protected PriorityQueue $filters;

    /**
     * ChainFilter constructor.
     *
     * @param  array  $filters
     */
    public function __construct(array $filters = [])
    {
        $this->resetFilters();

        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * addFilter
     *
     * @param  callable|FilterInterface  $filter
     * @param  int                       $priority
     *
     * @return ChainFilter
     */
    public function addFilter(FilterInterface|callable $filter, int $priority = PriorityQueue::NORMAL)
    {
        if (!$filter instanceof FilterInterface) {
            $filter = new CallbackFilter($filter);
        }

        $this->filters->insert($filter, $priority);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        foreach (clone $this->filters as $filter) {
            $value = $filter->filter($value);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function test(mixed $value, bool $strict = false): bool
    {
        foreach (clone $this->filters as $filter) {
            if (!$filter->test($value, $strict)) {
                throw ValidateException::create(
                    $filter,
                    'Validator: ' . TypeCast::forceString($filter) . ' returns false, value is: ' . Assert::describeValue($value)
                );
            }
        }

        return true;
    }

    public function resetFilters(): void
    {
        $this->filters = new PriorityQueue();
    }

    /**
     * @return FilterInterface[]|ValidatorInterface[]|PriorityQueue
     */
    public function getFilters(): PriorityQueue
    {
        return $this->filters;
    }
}
