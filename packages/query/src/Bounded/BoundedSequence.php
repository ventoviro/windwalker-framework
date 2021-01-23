<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Bounded;

/**
 * The BoundedSequence class.
 */
class BoundedSequence
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var int
     */
    protected $index;

    /**
     * BoundedSequence constructor.
     *
     * @param  string  $prefix
     * @param  int     $index
     */
    public function __construct(string $prefix, int $index = 0)
    {
        $this->prefix = $prefix;
        $this->index = $index;
    }

    public function get(): string
    {
        $serial = $this->peek();

        $this->index++;

        return $serial;
    }

    public function peek(): string
    {
        return $this->prefix . $this->index;
    }

    /**
     * Method to get property Prefix
     *
     * @return  string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Method to set property prefix
     *
     * @param  string  $prefix
     *
     * @return  static  Return self to support chaining.
     */
    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Method to get property Index
     *
     * @return  int
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * Method to set property index
     *
     * @param  int  $index
     *
     * @return  static  Return self to support chaining.
     */
    public function setIndex(int $index): static
    {
        $this->index = $index;

        return $this;
    }
}
