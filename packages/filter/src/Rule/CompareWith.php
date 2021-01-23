<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractFilter;
use Windwalker\Utilities\Compare\CompareHelper;

/**
 * The CompareWith class.
 */
class CompareWith extends AbstractFilter
{
    /**
     * Property operator.
     *
     * @var  string
     */
    protected ?string $operator = null;

    /**
     * Property compare.
     *
     * @var  mixed|null
     */
    protected $compare;

    /**
     * Property strict.
     *
     * @var  bool
     */
    protected bool $strict;

    /**
     * CompareValidator constructor.
     *
     * @param mixed   $compareA
     * @param string  $operator
     * @param bool    $strict
     *
     * @throws \DomainException
     */
    public function __construct($compareA = null, ?string $operator = null, bool $strict = false)
    {
        $this->compare = $compareA;
        $this->setOperator($operator);
        $this->setStrict($strict);
    }

    /**
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        return CompareHelper::compare($this->compare, $value, $this->operator, $this->strict);
    }

    /**
     * Method to get property Operator
     *
     * @return  string
     */
    public function getOperator(): ?string
    {
        return $this->operator;
    }

    /**
     * Method to set property operator
     *
     * @param   string $operator
     *
     * @return  static  Return self to support chaining.
     */
    public function setOperator(?string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Method to get property Compare
     *
     * @return  mixed|null
     */
    public function getCompare(): mixed
    {
        return $this->compare;
    }

    /**
     * Method to set property compare
     *
     * @param   mixed|null $compare
     *
     * @return  static  Return self to support chaining.
     */
    public function setCompare(mixed $compare): static
    {
        $this->compare = $compare;

        return $this;
    }

    /**
     * Method to set property strict
     *
     * @param   bool $strict
     *
     * @return  static  Return self to support chaining.
     */
    public function setStrict(bool $strict): static
    {
        $this->strict = $strict;

        return $this;
    }
}
