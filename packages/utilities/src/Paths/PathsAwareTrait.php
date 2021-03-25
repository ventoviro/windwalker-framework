<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Paths;

use SplPriorityQueue;
use Windwalker\Filesystem\Path;
use Windwalker\Utilities\Iterator\PriorityQueue;

/**
 * Trait PathsAwareTrait
 */
trait PathsAwareTrait
{
    /**
     * Property paths.
     *
     * @var PriorityQueue
     */
    protected PriorityQueue $paths;

    /**
     * getPaths
     *
     * @return  PriorityQueue
     */
    public function getPaths(): PriorityQueue
    {
        return $this->paths ??= new PriorityQueue();
    }

    /**
     * setPaths
     *
     * @param  array|string|SplPriorityQueue  $paths
     *
     * @return static Return self to support chaining.
     */
    public function setPaths(array|string|SplPriorityQueue $paths): static
    {
        if ($paths instanceof SplPriorityQueue) {
            $paths = new PriorityQueue($paths);
        }

        if (!$paths instanceof PriorityQueue) {
            $priority = new PriorityQueue();

            foreach ((array) $paths as $i => $path) {
                $priority->insert(Path::normalize($path), PriorityQueue::ABOVE_NORMAL - ($i * 10));
            }

            $paths = $priority;
        }

        $this->paths = $paths;

        return $this;
    }

    /**
     * addPath
     *
     * @param  string|array  $path
     * @param  integer       $priority
     *
     * @return  static
     */
    public function addPath(string|array $path, int $priority = PriorityQueue::ABOVE_NORMAL): static
    {
        $path = (array) $path;
        $queue = $this->getPaths();

        foreach ($path as $p) {
            $queue->insert($p, $priority);
        }

        return $this;
    }

    public function mergePaths(SplPriorityQueue $queue): static
    {
        $this->getPaths()->merge($queue);

        return $this;
    }

    /**
     * clearPaths
     *
     * @return  static
     */
    public function clearPaths(): static
    {
        $this->setPaths([]);

        return $this;
    }

    public function getClonedPaths(): SplPriorityQueue
    {
        return clone $this->getPaths();
    }

    /**
     * dumpPaths
     *
     * @return  array
     */
    public function dumpPaths(): array
    {
        $paths = $this->getClonedPaths();

        $return = [];

        foreach ($paths as $path) {
            $return[] = $path;
        }

        return $return;
    }
}
