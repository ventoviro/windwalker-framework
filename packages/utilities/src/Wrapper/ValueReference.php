<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Wrapper;

use Windwalker\Data\Collection;
use Windwalker\Utilities\Arr;

/**
 * The ValueReference class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ValueReference implements WrapperInterface
{
    /**
     * Property path.
     *
     * @var  string
     */
    public string $path;

    /**
     * Property separator.
     *
     * @var  string|null
     */
    public ?string $delimiter;

    /**
     * ValueReference constructor.
     *
     * @param  string       $path
     * @param  string|null  $delimiter
     */
    public function __construct(string $path, ?string $delimiter = '.')
    {
        $this->path      = $path;
        $this->delimiter = $delimiter;
    }

    /**
     * Get wrapped value.
     *
     * @param  array|object  $src
     * @param  string|null   $delimiter
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __invoke(mixed $src, ?string $delimiter = null): mixed
    {
        if ($src instanceof Collection) {
            return $src->getDeep($this->path, (string) ($delimiter ?? $this->delimiter));
        }

        return Arr::get($src, $this->path, (string) ($delimiter ?? $this->delimiter));
    }

    /**
     * Method to get property Delimiter
     *
     * @return  string|null
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getDelimiter(): ?string
    {
        return $this->delimiter;
    }

    /**
     * Method to get property Path
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
