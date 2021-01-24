<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field\Concern;

use InvalidArgumentException;
use Windwalker\Filter\ChainFilter;
use Windwalker\Filter\Exception\ValidateException;
use Windwalker\Filter\FilterInterface;
use Windwalker\Filter\Traits\FilterAwareTrait;
use Windwalker\Filter\ValidatorInterface;
use Windwalker\Form\ValidateResult;

/**
 * Trait ManageFilterTrait
 */
trait ManageFilterTrait
{
    use FilterAwareTrait;

    protected ChainFilter $validator;

    protected ChainFilter $filter;

    protected ChainFilter $viewFilter;

    /**
     * validate
     *
     * @param  mixed  $value
     *
     * @return  ValidateResult
     */
    public function validate(mixed $value): ValidateResult
    {
        if ($this->isDisabled()) {
            return new ValidateResult(ValidateResult::STATUS_SUCCESS, $this);
        }

        if ($this->isRequired() && !$this->checkRequired($value)) {
            return new ValidateResult(ValidateResult::STATUS_REQUIRED, $this);
        }

        if ($value !== null && $value !== '') {
            try {
                $this->testValidator($value);
            } catch (ValidateException $e) {
                return new ValidateResult(ValidateResult::STATUS_FAILURE, $this, $e->getValidator());
            }
        }

        return new ValidateResult(ValidateResult::STATUS_SUCCESS, $this);
    }

    /**
     * checkRequired
     *
     * @param  mixed  $value
     *
     * @return  bool
     */
    public function checkRequired(mixed $value): bool
    {
        return !in_array($value, $this->getEmptyValues(), true);
    }

    public function addEmptyValues(...$args): static
    {
        $values = $this->getState('emptyValues', ['', null, '0', 0, []]);

        $values = array_unique(array_merge($values, $args));

        $this->setState('emptyValues', $values);

        return $this;
    }

    public function getEmptyValues(): array
    {
        return (array) ($this->getState('emptyValues') ?? ['', null, '0', 0]);
    }

    /**
     * checkRule
     *
     * @param  mixed  $value
     *
     * @return  bool
     */
    public function testValidator(mixed $value): bool
    {
        return $this->getValidator()->test($value);
    }

    /**
     * filter
     *
     * @param  mixed  $value
     *
     * @return mixed
     */
    public function filter(mixed $value): mixed
    {
        if ($value === null || $this->isDisabled()) {
            return $value;
        }

        return $this->getFilter()->filter($value);
    }

    /**
     * addValidator
     *
     * @param  ValidatorInterface|callable|string  $validators
     *
     * @return  static
     * @throws InvalidArgumentException
     */
    public function addValidator(ValidatorInterface|callable|string ...$validators): static
    {
        foreach ($validators as $validator) {
            $this->validator->addFilter($this->getFilterFactory()->create($validator));
        }

        return $this;
    }

    /**
     * Method to get property Rule
     *
     * @return  ChainFilter
     */
    public function getValidator(): ChainFilter
    {
        return $this->validator;
    }

    /**
     * resetValidators
     *
     * @return  static
     */
    public function resetValidators(): static
    {
        $this->validator = new ChainFilter();

        return $this;
    }

    /**
     * addFilter
     *
     * @param  FilterInterface|callable|string  ...$filters
     *
     * @return  static
     * @throws InvalidArgumentException
     */
    public function addFilter(FilterInterface|callable|string ...$filters): static
    {
        foreach ($filters as $filter) {
            $this->filter->addFilter($this->getFilterFactory()->create($filter));
        }

        return $this;
    }

    /**
     * Method to get property Filter
     *
     * @return  ChainFilter
     */
    public function getFilter(): ChainFilter
    {
        return $this->filter;
    }

    /**
     * resetFilters
     *
     * @return  static
     */
    public function resetFilters(): static
    {
        $this->filter = new ChainFilter();

        return $this;
    }

    /**
     * Method to get property ValueFilter
     *
     * @return  ChainFilter
     *
     * @since  3.5.21
     */
    public function getViewFilter(): ChainFilter
    {
        return $this->viewFilter;
    }

    /**
     * Method to set property valueFilter
     *
     * @param  ChainFilter  $viewFilter
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.5.21
     */
    public function setViewFilter(ChainFilter $viewFilter): static
    {
        $this->viewFilter = $viewFilter;

        return $this;
    }

    /**
     * addFilter
     *
     * @param  FilterInterface|callable|string  ...$filters
     *
     * @return  static
     * @throws InvalidArgumentException
     */
    public function addViewFilter(FilterInterface|callable|string ...$filters): static
    {
        foreach ($filters as $filter) {
            $this->viewFilter->addFilter($this->getFilterFactory()->create($filter));
        }

        return $this;
    }

    /**
     * resetValueFilters
     *
     * @return  static
     *
     * @since  3.5.21
     */
    public function resetViewFilters(): static
    {
        $this->viewFilter = new ChainFilter();

        return $this;
    }
}
