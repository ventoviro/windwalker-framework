<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Handler;

use Windwalker\Filesystem\FileObject;
use Windwalker\Filesystem\Filesystem;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The FilesystemHandler class.
 */
class FilesystemHandler extends AbstractHandler
{
    use OptionAccessTrait;

    protected ?string $path;

    /**
     * FilesystemHandler constructor.
     *
     * @param  string|null  $path
     * @param  array        $options
     */
    public function __construct(?string $path = null, array $options = [])
    {
        if (!class_exists(Filesystem::class)) {
            throw new \DomainException('Please install windwalker/filesystem ^4.0');
        }

        $this->prepareOptions(
            [
                'prefix' => 'sess_'
            ],
            $options
        );

        $this->path = $path;
    }

    public function getFileName(string $id): string
    {
        $file = $id;

        if ($this->getOption('prefix')) {
            $file = $this->getOption('prefix') . $file;
        }

        return $file;
    }

    public function getFilePath(string $id): string
    {
        $this->path ??= session_save_path();

        return $this->path . '/' . $this->getFileName($id);
    }

    /**
     * doRead
     *
     * @param  string  $id
     *
     * @return  string|null
     */
    protected function doRead(string $id): ?string
    {
        $file = Filesystem::get($this->getFilePath($id));

        if (!$file->exists()) {
            return null;
        }

        return (string) $file->read();
    }

    /**
     * isSupported
     *
     * @return  bool
     */
    public static function isSupported(): bool
    {
        return !class_exists(Filesystem::class);
    }

    /**
     * destroy
     *
     * @param  string  $id
     *
     * @return  bool
     */
    public function destroy($id): bool
    {
        Filesystem::delete($this->getFilePath($id));

        return true;
    }

    /**
     * gc
     *
     * @param  int  $maxlifetime
     *
     * @return  bool
     */
    public function gc($maxlifetime): bool
    {
        $past = time() - $maxlifetime;

        $files = Filesystem::files($this->path)
            ->filter(fn (FileObject $file) => $file->getMTime() < $past);

        /** @var FileObject $file */
        foreach ($files as $file) {
            $file->delete();
        }

        return true;
    }

    /**
     * write
     *
     * @param  string  $id
     * @param  string  $data
     *
     * @return  bool
     */
    public function write($id, $data): bool
    {
        Filesystem::write($this->getFilePath($id), $data);

        return true;
    }

    /**
     * updateTimestamp
     *
     * @param  string  $id
     * @param  string  $data
     *
     * @return  bool
     */
    public function updateTimestamp($id, $data): bool
    {
        $file = Filesystem::get($this->getFilePath($id));

        if (!$file->exists()) {
            return false;
        }

        $file->touch(time());

        return true;
    }
}
