<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Data;

use Windwalker\Scalars\ArrayObject;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Assert\ArgumentsAssert;

/**
 * The MapProxy class.
 */
class MapProxy
{
    /**
     * @var Collection
     */
    protected Collection $collection;

    /**
     * @var string|null
     */
    protected ?string $column = null;

    /**
     * MapProxy constructor.
     *
     * @param  Collection   $collection
     * @param  string|null  $column
     */
    public function __construct(Collection $collection, ?string $column = null)
    {
        $this->collection = $collection;
        $this->column = $column;
    }

    private function mapCollection(string $name, array $args): Collection
    {
        return $this->collection->map(function ($item) use ($name, $args) {
            return $item->$name(...$args);
        });
    }

    private function mapColumn(string $name, array $args): Collection
    {
        $items = $this->collection
            ->column($this->column)
            ->$name(...$args);

        ArgumentsAssert::assert(
            $items instanceof Collection,
            sprintf(
                'Collection::proxyMap(...)->%s(...) not supported. ' .
                'You must call a method which will return Collection itself or new instance',
                $name
            )
        );

        $new = $this->collection;

        foreach ($items as $key => $value) {
            $item = $new[$key];

            if ($item instanceof Collection) {
                $item = $item->with($this->column, $value);
            } else {
                $item = Arr::set($item, $this->column, $value);
            }

            $new = $new->with($key, $item);
        }

        return $new;
    }

    public function __call(string $name, array $args): Collection
    {
        if ($this->column === null) {
            return $this->mapCollection($name, $args);
        }

        return $this->mapColumn($name, $args);
    }
}
