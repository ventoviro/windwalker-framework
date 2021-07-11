<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\DI;

use Generator;
use Windwalker\Data\Collection;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\TypeCast;
use Windwalker\Utilities\Wrapper\ValueReference;

use function Windwalker\tap;

/**
 * The Config class.
 */
class Parameters extends Collection
{
    /**
     * Make sure storage is first.
     *
     * @var array
     */
    protected mixed $storage = [];

    protected ?Parameters $parent = null;

    public function createChild(): static
    {
        $new = clone $this;
        $new->parent = $this;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function extract(?string $path = null, bool $reference = false): static
    {
        return tap(
            parent::extract($path, $reference),
            function ($new) use ($reference) {
                if ($reference) {
                    $new->parent = $this;
                }
            }
        );
    }

    /**
     * getDeep
     *
     * @param  string  $path
     * @param  string  $delimiter
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function &getDeep(string $path, string $delimiter = '.'): mixed
    {
        $value = parent::getDeep($path, $delimiter);

        if ($value === null && $this->parent) {
            $value = $this->parent->getDeep($path, $delimiter);
        }

        while ($value instanceof ValueReference) {
            $value = $value($this, $value->getDelimiter() ?? $delimiter);
        }

        return $value;
    }

    /**
     * Get value from this object.
     *
     * @param  mixed  $key
     *
     * @return  mixed
     */
    public function &get(mixed $key): mixed
    {
        $value = parent::get($key);

        if ($value === null && $this->parent) {
            $value = $this->parent->get($key);
        }

        while ($value instanceof ValueReference) {
            $value = $value($this);
        }

        return $value;
    }

    public function hasDeep(string $path, ?string $delimiter = '.'): bool
    {
        return $this->getDeep($path, $delimiter) !== null;
    }

    public function has(mixed $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Method to get property Parent
     *
     * @return  Parameters|null
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getParent(): ?Parameters
    {
        return $this->parent;
    }

    /**
     * @inheritDoc
     */
    public function &getIterator(bool $includeParent = true): Generator
    {
        foreach ($this->storage as $key => &$value) {
            yield $key => $value;
        }

        unset($value);

        if ($this->parent && $includeParent) {
            foreach ($this->parent as $key => &$value) {
                yield $key => $value;
            }
        }
    }

    /**
     * Creates a copy of storage.
     *
     * @param  bool  $recursive
     *
     * @param  bool  $onlyDumpable
     *
     * @return array
     */
    public function dump(bool $recursive = false, bool $onlyDumpable = false): array
    {
        $data = $this->storage;

        $data = Arr::mapRecursive(
            $data,
            function ($v) use ($data) {
                if ($v instanceof ValueReference) {
                    return Arr::get($data, $v->getPath(), $v->getDelimiter());
                }

                return $v;
            }
        );

        if ($recursive) {
            $data = TypeCast::toArray($data, true, $onlyDumpable);
        }

        if ($this->parent) {
            $data = Arr::mergeRecursive(
                $this->parent->dump($recursive, $onlyDumpable),
                $data
            );
        }

        return $data;
    }
}
