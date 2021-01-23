<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

use League\Flysystem\Filesystem;
use phpDocumentor\Reflection\Types\True_;

/**
 * The FlysystemStorage class.
 */
class FlysystemStorage extends FileStorage
{
    /**
     * @var Filesystem
     */
    protected Filesystem $driver;

    /**
     * FlysystemStorage constructor.
     *
     * @param  Filesystem  $driver
     * @param  array       $options
     */
    public function __construct(Filesystem $driver, array $options = [])
    {
        $this->driver = $driver;

        parent::__construct('.', $options);
    }

    /**
     * @inheritDoc
     */
    protected function read(string $key): string
    {
        return (string) $this->getDriver()->read($this->fetchStreamUri($key));
    }

    /**
     * @inheritDoc
     */
    protected function write(string $key, string $value): bool
    {
        $this->getDriver()->write($this->fetchStreamUri($key), $value);

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function exists(string $key): bool
    {
        return $this->getDriver()->fileExists($this->fetchStreamUri($key));
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $results = true;

        foreach ($this->getDriver()->listContents('/', true) as $metadata) {
            $results = $this->getDriver()->delete($metadata['path']) && $results;
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        $this->getDriver()->delete($this->fetchStreamUri($key));

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function checkFilePath($filePath): bool
    {
        return true;
    }

    /**
     * Method to get property Driver
     *
     * @return  Filesystem
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getDriver(): Filesystem
    {
        return $this->driver;
    }

    /**
     * Method to set property driver
     *
     * @param  Filesystem  $driver
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setDriver(Filesystem $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Get the full stream URI for the cache entry.
     *
     * @param  string  $key  The storage entry identifier.
     *
     * @return  string  The full stream URI for the cache entry.
     *
     * @throws  \RuntimeException if the cache path is invalid.
     * @since   2.0
     */
    public function fetchStreamUri(string $key): string
    {
        $filePath = $this->getRoot();

        $this->checkFilePath($filePath);

        $ext = '.data';

        if ($this->getOption('deny_access', false)) {
            $ext = '.php';
        }

        return self::hashFilename($key) . $ext;
    }

    /**
     * hashFilename
     *
     * @param  string  $key
     *
     * @return  string
     */
    public static function hashFilename(string $key): string
    {
        return '~' . hash('sha1', $key);
    }
}
