<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

use SplFileInfo;

/**
 * The ModifiedFileStorage class.
 */
class ModifiedFileStorage extends FileStorage
{
    /**
     * AbstractFormatterStorage constructor.
     *
     * @param  string  $root
     * @param  array   $options
     */
    public function __construct(
        string $root,
        protected array $listenFiles,
        array $options = []
    ) {
        parent::__construct($root, $options);
    }

    public function isModified(string $key): bool
    {
        $uri  = $this->fetchStreamUri($key);
        $time = filemtime($uri);

        $lastModified = 0;

        foreach ($this->listenFiles as $listenFile) {
            if (is_string($listenFile)) {
                $listenFile = new SplFileInfo($listenFile);
            }

            $mTime = $listenFile->getMTime();
            if ($mTime > $lastModified) {
                $lastModified = $mTime;
            }
        }

        return $lastModified > $time;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        if ($this->isModified($key)) {
            return false;
        }

        return parent::has($key);
    }

    /**
     * @return array
     */
    public function getListenFiles(): array
    {
        return $this->listenFiles;
    }

    /**
     * @param  array  $listenFiles
     *
     * @return  static  Return self to support chaining.
     */
    public function setListenFiles(array $listenFiles): static
    {
        $this->listenFiles = $listenFiles;

        return $this;
    }
}
