<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Iterator;

use Windwalker\Utilities\Test\Iterator\NestedIteratorTest;

/**
 * Rewindable Generator.
 *
 * This is a simple wrapper of generator factory. Generator is un-rewindable, so you can create this
 * Iterator with a generator factory callback. Every time this iterator rewind, the factory callback
 * will execute again to create a new Generator.
 *
 * ```php
 * $iter = new RewindableGenerator(fn () => yield ...);
 * iterator_to_array($iter);
 *
 * $iter->rewind(); // Will re-create a new Generator.
 * ```
 *
 * @test {@see NestedIteratorTest}
 */
class RewindableGenerator implements \OuterIterator
{
    /**
     * @var callable
     */
    protected $callable;

    protected ?\Generator $generator = null;

    /**
     * RewindableGenerator constructor.
     *
     * @param  callable  $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * getGenerator
     *
     * @param  bool  $refresh
     *
     * @return  \Generator
     */
    protected function getGenerator(bool $refresh = false): \Generator
    {
        if (!$this->generator || $refresh) {
            $this->generator = ($this->callable)();
        }

        return $this->generator;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->getGenerator()->current();
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->getGenerator()->next();
    }

    /**
     * @inheritDoc
     */
    public function key(): float|bool|int|string|null
    {
        return $this->getGenerator()->key();
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->getGenerator()->valid();
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->getGenerator(true);
    }

    /**
     * @inheritDoc
     */
    public function getInnerIterator(): \Iterator|\Generator
    {
        return $this->getGenerator();
    }
}
