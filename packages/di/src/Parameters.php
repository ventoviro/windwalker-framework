<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\DI;

use Windwalker\Data\Collection;
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
}
