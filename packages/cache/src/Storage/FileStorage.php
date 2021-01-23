<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

use RuntimeException;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The FilesystemStorage class.
 */
class FileStorage implements StorageInterface
{
    use OptionAccessTrait;

    /**
     * @var string
     */
    protected string $root;

    /**
     * AbstractFormatterStorage constructor.
     *
     * @param  string  $root
     * @param  array   $options
     */
    public function __construct(string $root, array $options = [])
    {
        $this->root = $root;

        $this->prepareOptions(
            [
                'lock' => false,
                'extension' => '.data',
                'expiration_format' => '/////---------- Expired At: %s ----------/////%s',
            ],
            $options
        );

        $this->checkFilePath($root);
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        $data = $this->read($key);

        sscanf($data, $this->getOption('expiration_format'), $expiration, $value);

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        if (!$this->exists($key)) {
            return false;
        }

        $data = $this->read($key);

        sscanf($data, $this->getOption('expiration_format'), $expiration, $value);

        if (!static::isExpired((int) $expiration)) {
            return true;
        }

        $this->remove($key);

        return false;
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $filePath = $this->getRoot();
        $this->checkFilePath($filePath);

        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($filePath)
            ),
            '/' . preg_quote($this->getOption('extension')) . '$/i'
        );

        $results = true;

        /* @var  \RecursiveDirectoryIterator $file */
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $results = unlink($file->getRealPath()) && $results;
            }
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        return unlink($this->fetchStreamUri($key));
    }

    /**
     * @inheritDoc
     */
    public function save(string $key, mixed $value, int $expiration = 0): bool
    {
        if ($this->getOption('deny_access', false)) {
            $value = $this->getOption('deny_code') . $value;
        }

        $expirationFormat = $this->getOption('expiration_format');

        $value = sprintf($expirationFormat, $expiration, $value);

        return $this->write($key, $value);
    }

    /**
     * Check that the file path is a directory and writable.
     *
     * @param  string  $filePath  A file path.
     *
     * @return  boolean  The method will always return true, if it returns.
     *
     * @throws  RuntimeException if the file path is invalid.
     * @since   2.0
     */
    protected function checkFilePath($filePath): bool
    {
        if (!is_dir($filePath)) {
            try {
                mkdir($filePath, 0755, true);
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    sprintf('Directory "%s" was not created with error: %s', $filePath, $e->getMessage()),
                    $e->getCode(),
                    $e
                );
            }
        }

        if (!is_writable($filePath)) {
            throw new RuntimeException(sprintf('The base cache path `%s` is not writable.', $filePath));
        }

        return true;
    }

    /**
     * write
     *
     * @param  string  $key
     * @param  string  $value
     *
     * @return  boolean
     */
    protected function write(string $key, string $value): bool
    {
        $filename = $this->fetchStreamUri($key);

        return (bool) file_put_contents(
            $filename,
            $value,
            $this->getOption('lock', false) ? LOCK_EX : 0
        );
    }

    /**
     * read
     *
     * @param  string  $key
     *
     * @return  string
     */
    protected function read(string $key): string
    {
        $filename = $this->fetchStreamUri($key);

        $resource = @fopen($filename, 'rb');

        if (!$resource) {
            throw new RuntimeException(
                sprintf(
                    'Unable to fetch cache entry for %s. Cannot open the resource.',
                    $filename
                )
            );
        }

        // If locking is enabled get a shared lock for reading on the resource.
        if ($this->getOption('lock', false) && !flock($resource, LOCK_SH)) {
            throw new RuntimeException(
                sprintf(
                    'Unable to fetch cache entry for %s. Cannot obtain a lock.',
                    $filename
                )
            );
        }

        $data = stream_get_contents($resource);

        // If locking is enabled release the lock on the resource.
        if ($this->getOption('lock', false) && !flock($resource, LOCK_UN)) {
            throw new RuntimeException(
                sprintf(
                    'Unable to fetch cache entry for %s. Cannot release the lock.',
                    $filename
                )
            );
        }

        fclose($resource);

        return $data;
    }

    /**
     * exists
     *
     * @param  string  $key
     *
     * @return  bool
     */
    protected function exists(string $key): bool
    {
        return is_file($this->fetchStreamUri($key));
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

        if ($this->getOption('deny_access', false)) {
            $this->config['extension'] = '.php';
        }

        return sprintf(
            '%s/%s' . $this->getOption('extension'),
            $filePath,
            self::hashFilename($key)
        );
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

    /**
     * getRoot
     *
     * @return  string
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * isExpired
     *
     * @param  int       $expiration
     * @param  int|null  $time
     *
     * @return  bool
     */
    public static function isExpired(int $expiration, ?int $time = null): bool
    {
        $time ??= time();

        return $expiration !== 0 && $expiration <= $time;
    }
}
