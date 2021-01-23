<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Iterator;

/**
 * The MultiLevelIterator class.
 */
class NestedIterator implements \OuterIterator
{
    protected \Traversable $innerIterator;

    protected ?\Iterator $compiledIterator = null;

    /**
     * @var callable[]
     */
    protected array $callbacks = [];

    /**
     * FilesIterator constructor.
     *
     * @param  iterable|callable  $iterator
     */
    public function __construct(iterable|callable $iterator)
    {
        if (is_callable($iterator)) {
            $iterator = new RewindableGenerator($iterator);
        }

        $this->innerIterator = $iterator instanceof \Traversable
            ? $iterator
            : new \ArrayIterator($iterator);
    }

    /**
     * wrap
     *
     * @param  callable  $callback
     *
     * @return  static
     */
    public function wrap(callable $callback): static
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * wrap
     *
     * @param  callable  $callback
     *
     * @return  static
     */
    public function with(callable $callback): static
    {
        $new = $this->cloneNew();

        $new->callbacks   = $this->callbacks;
        $new->callbacks[] = $callback;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getInnerIterator(): \Iterator
    {
        return $this->innerIterator;
    }

    /**
     * compileIterator
     *
     * @param  bool  $refresh
     *
     * @return  \Traversable
     */
    protected function compileIterator(bool $refresh = false): \Traversable
    {
        if ($this->compiledIterator === null || $refresh) {
            if ($this->checkRewindable($this->innerIterator)) {
                $this->innerIterator->rewind();
            }

            $iterator = $this->innerIterator;

            foreach ($this->callbacks as $callback) {
                $iterator = (static function () use ($iterator, $callback) {
                    return $callback($iterator);
                })();
            }

            $this->compiledIterator = $iterator;
        }

        return $this->compiledIterator;
    }

    /**
     * filter
     *
     * @param  callable  $callback
     * @param  int       $flags
     *
     * @return  static
     */
    public function filter(callable $callback, int $flags = 0): static
    {
        return $this->with(
            function (iterable $files) use ($flags, $callback) {
                foreach ($files as $key => $item) {
                    if ($flags === ARRAY_FILTER_USE_BOTH) {
                        $result = $callback($item, $key);
                    } elseif ($flags === ARRAY_FILTER_USE_KEY) {
                        $result = $callback($key);
                    } else {
                        $result = $callback($item);
                    }

                    if ($result) {
                        yield $key => $item;
                    }
                }
            }
        );
    }

    public function map(callable $callback): static
    {
        return $this->with(
            function (iterable $items) use ($callback) {
                foreach ($items as $key => $item) {
                    yield $key => $callback($item);
                }
            }
        );
    }

    public function mapWithKey(callable $callback): static
    {
        return $this->with(
            function (iterable $items) use ($callback) {
                foreach ($items as $key => $item) {
                    $result = (array) $callback($item, $key);

                    $k = array_key_first($result);
                    $value = $result[$k];

                    yield $k => $value;
                }
            }
        );
    }

    public function chunk(int $size, bool $preserveKeys = false): static
    {
        return $this->with(
            static function (\Iterator $iter) use ($preserveKeys, $size) {
                // @see https://blog.kevingomez.fr/2016/02/26/efficiently-creating-data-chunks-in-php/
                $closure = static function() use ($preserveKeys, $iter, $size) {
                    $count = $size;
                    while ($count-- && $iter->valid()) {
                        if ($preserveKeys) {
                            yield $iter->key() => $iter->current();
                        } else {
                            yield $iter->current();
                        }

                        $iter->next();
                    }
                };

                while ($iter->valid()) {
                    yield $closure();
                }
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->compileIterator()->current();
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->compileIterator()->next();
    }

    /**
     * @inheritDoc
     */
    public function key(): float|bool|int|string|null
    {
        return $this->compileIterator()->key();
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->compileIterator()->valid();
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->compileIterator(true);
    }

    /**
     * This iterator unable to use native clone. We clone it manually.
     *
     * @return  static
     */
    protected function cloneNew(): static
    {
        return new static($this->getInnerIterator());
    }

    /**
     * Method to set property innerIterator
     *
     * @param  \Traversable  $innerIterator
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setInnerIterator(\Traversable $innerIterator): static
    {
        $this->innerIterator = $innerIterator;

        return $this;
    }

    /**
     * checkRewindable
     *
     * @param  \Traversable  $iter
     *
     * @return  bool
     */
    protected function checkRewindable(\Traversable $iter): bool
    {
        if ($iter instanceof \Generator) {
            return false;
        }

        if ($iter instanceof \OuterIterator) {
            return $this->checkRewindable($iter->getInnerIterator());
        }

        return $iter instanceof \Iterator;
    }
}
