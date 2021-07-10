<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Loader;

use Windwalker\Edge\Exception\LayoutNotFoundException;

/**
 * The EdgeFileLoader class.
 *
 * @since  3.0
 */
class EdgeFileLoader implements EdgeLoaderInterface
{
    /**
     * Property extensions.
     *
     * @var  array
     */
    protected array $extensions = [
        'edge.php',
        'blade.php',
    ];

    /**
     * Property paths.
     *
     * @var  array
     */
    protected array $paths = [];

    /**
     * EdgeFileLoader constructor.
     *
     * @param  array       $paths
     * @param  array|null  $extensions
     */
    public function __construct(array $paths = [], ?array $extensions = null)
    {
        $this->paths = $paths;

        if ($extensions !== null) {
            $this->extensions = $extensions;
        }
    }

    /**
     * find
     *
     * @param  string  $key
     *
     * @return  string
     */
    public function find(string $key): string
    {
        $filePath = $this->doFind($key);

        if ($filePath === null) {
            $paths = implode(" |\n ", $this->paths);

            throw new LayoutNotFoundException('View file not found: ' . $key . ".\n (Paths: " . $paths . ')', 13001);
        }

        return $filePath;
    }

    public function doFind(string $key): ?string
    {
        $key = $this->normalize($key);

        $filePath = null;

        foreach ($this->paths as $path) {
            foreach ($this->extensions as $ext) {
                if (is_file($path . '/' . $key . '.' . $ext)) {
                    $filePath = $path . '/' . $key . '.' . $ext;

                    break 2;
                }
            }
        }

        return $filePath;
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
        return file_get_contents($path);
    }

    /**
     * addPath
     *
     * @param  string  $path
     *
     * @return  static
     */
    public function addPath(string $path): static
    {
        $this->paths[] = $path;

        return $this;
    }

    /**
     * prependPath
     *
     * @param  string  $path
     *
     * @return  static
     */
    public function prependPath(string $path): static
    {
        array_unshift($this->paths, $path);

        return $this;
    }

    /**
     * normalize
     *
     * @param  string  $path
     *
     * @return  string
     */
    protected function normalize(string $path): string
    {
        return str_replace('.', '/', $path);
    }

    /**
     * Method to get property Paths
     *
     * @return  array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Method to set property paths
     *
     * @param  array  $paths
     *
     * @return  static  Return self to support chaining.
     */
    public function setPaths(array $paths): static
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * addExtension
     *
     * @param  string  $name
     *
     * @return  static
     */
    public function addFileExtension(string $name): static
    {
        $this->extensions[] = $name;

        return $this;
    }

    /**
     * Method to get property Extensions
     *
     * @return  array
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Method to set property extensions
     *
     * @param  array  $extensions
     *
     * @return  static  Return self to support chaining.
     */
    public function setExtensions(array $extensions): static
    {
        $this->extensions = $extensions;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        try {
            return $this->doFind($key) !== null;
        } catch (LayoutNotFoundException) {
            return false;
        }
    }
}
