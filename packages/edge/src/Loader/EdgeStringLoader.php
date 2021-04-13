<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Loader;

/**
 * The EdgeFileLoader class.
 *
 * @since  3.0
 */
class EdgeStringLoader implements EdgeLoaderInterface
{
    /**
     * Property content.
     *
     * @var  string
     */
    protected string $content;

    /**
     * EdgeTextLoader constructor.
     *
     * @param  string  $content
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * load
     *
     * @param  string  $key
     *
     * @return  string
     */
    public function find(string $key): string
    {
        return $key;
    }

    /**
     * loadFile
     *
     * @param  string  $path
     *
     * @return  string
     */
    public function load(string $path): string
    {
        return $path ?: $this->content;
    }

    /**
     * Method to get property Content
     *
     * @return  string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Method to set property content
     *
     * @param  string  $content
     *
     * @return  static  Return self to support chaining.
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return true;
    }
}
