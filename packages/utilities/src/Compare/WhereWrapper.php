<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Compare;

use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Wrapper\WrapperInterface;

/**
 * The CompareWrapper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class WhereWrapper implements WrapperInterface
{
    /**
     * @var  string
     */
    protected string $operator;

    /**
     * @var mixed
     */
    protected $var1;

    /**
     * @var mixed
     */
    protected $var2;

    /**
     * @var  bool
     */
    protected bool $strict;

    /**
     * CompareWrapper constructor.
     *
     * @param  mixed   $var1
     * @param  string  $operator
     * @param  mixed   $var2
     * @param  bool    $strict
     */
    public function __construct(mixed $var1, string $operator, mixed $var2, bool $strict = false)
    {
        $this->var1     = $var1;
        $this->operator = $operator;
        $this->var2     = $var2;
        $this->strict   = $strict;
    }

    /**
     * __invoke
     *
     * @param  array|object  $src
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __invoke(mixed $src): bool
    {
        return CompareHelper::compare(
            Arr::get($src, $this->var1, ''),
            $this->var2,
            $this->operator,
            $this->strict
        );
    }

    /**
     * Method to get property Var1
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getVar1(): mixed
    {
        return $this->var1;
    }

    /**
     * Method to set property var1
     *
     * @param  mixed  $var1
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setVar1(mixed $var1): static
    {
        $this->var1 = $var1;

        return $this;
    }

    /**
     * Method to get property Var2
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getVar2(): mixed
    {
        return $this->var2;
    }

    /**
     * Method to set property var2
     *
     * @param  mixed  $var2
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setVar2(mixed $var2): static
    {
        $this->var2 = $var2;

        return $this;
    }

    /**
     * Method to get property Operator
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Method to set property operator
     *
     * @param  string  $operator
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setOperator(string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }
}
