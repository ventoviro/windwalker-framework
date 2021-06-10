<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filesystem;

use Psr\Http\Message\StreamInterface;
use Windwalker\Filesystem\Exception\FileNotFoundException;
use Windwalker\Filesystem\Exception\FilesystemException;
use Windwalker\Filesystem\Iterator\FilesIterator;
use Windwalker\Promise\Promise;
use Windwalker\Scalars\StringObject;
use Windwalker\Stream\Stream;
use Windwalker\Stream\StreamHelper;
use Windwalker\Utilities\Assert\ArgumentsAssert;
use Windwalker\Utilities\Str;

/**
 * The FileObject class.
 *
 * @method Promise mkdirAsync(int $mode = 0755)
 * @method Promise copyAsync(string|\SplFileInfo $dest, bool $force = false)
 * @method Promise moveAsync(string|\SplFileInfo $dest, bool $force = false)
 * @method Promise readAsync(bool $useIncludePath = false, $context = null, int $offset = 0, ?int $maxlen = null)
 * @method Promise readStreamAsync(string $mode = Stream::MODE_READ_ONLY_FROM_BEGIN)
 * @method Promise writeAsync(string $buffer)
 * @method Promise writeStreamAsync(string|resource|StreamInterface $stream)
 * @method Promise deleteAsync()
 * @method Promise filesAsync(bool $recursive = false)
 * @method Promise foldersAsync(bool $recursive = false)
 * @method Promise itemsAsync(bool $recursive = false)
 * @method Promise getStreamAsync(string $mode = Stream::MODE_READ_WRITE_FROM_BEGIN)
 *
 */
class FileObject extends \SplFileInfo
{
    protected ?string $root;

    /**
     * unwrap
     *
     * @param  string|\SplFileInfo  $file
     *
     * @return  string
     */
    public static function unwrap(\SplFileInfo|string $file): string
    {
        if ($file instanceof \SplFileInfo) {
            if ($file->isDir()) {
                return Path::stripTrailingDot($file->getPathname());
            }

            return Path::stripTrailingDot($file->getPathname());
        }

        return (string) $file;
    }

    /**
     * wrap
     *
     * @param  string|\SplFileInfo  $file
     * @param  string|null          $root
     *
     * @return  static
     */
    public static function wrap(\SplFileInfo|string $file, ?string $root = null): static
    {
        if ($file instanceof self) {
            if ($root !== null) {
                $file->root = $root;
            }

            return $file;
        }

        if ($file instanceof \SplFileInfo) {
            $file = new static($file->getPathname());
        }

        return new static($file, $root);
    }

    /**
     * wrapIfNotNull
     *
     * @param  \SplFileInfo|string|null  $file
     * @param  string|null               $root
     *
     * @return  static|null
     */
    public static function wrapIfNotNull(\SplFileInfo|string|null $file, ?string $root = null): static|null
    {
        if ($file === null) {
            return null;
        }

        return static::wrap($file, $root);
    }

    /**
     * @inheritDoc
     */
    public function __construct($filename, ?string $root = null)
    {
        $filename = static::unwrap($filename);

        parent::__construct($filename);

        $this->root = $root;
    }

    /**
     * getRelativePathFrom
     *
     * @param  string|\SplFileInfo|null  $root
     *
     * @return  string
     */
    public function getRelativePathname(string|\SplFileInfo|null $root = null): string
    {
        $path = $this->getRelativePath($root);
        $basename = $this->getBasename();

        return $path . DIRECTORY_SEPARATOR . $basename;
    }

    /**
     * getRelativePath
     *
     * @param  string|\SplFileInfo|null  $root
     *
     * @return  string
     */
    public function getRelativePath(string|\SplFileInfo|null $root = null): string
    {
        if ($root === null) {
            $root = $this->root;
        }

        if ($root === null) {
            throw new \InvalidArgumentException('No root path provided');
        }

        $path = Path::normalize($this->getPathname());
        $root = Path::normalize(static::unwrap($root));

        if ((string) $root === '') {
            return $path;
        }

        if ($path === $root) {
            return '';
        }

        if (!str_starts_with($path, $root)) {
            return $path;
        }

        return ltrim(substr($path, strlen($root)), DIRECTORY_SEPARATOR);
    }

    /**
     * getPathname
     *
     * @return  string
     */
    public function getPathname(): string
    {
        return Str::removeRight(parent::getPathname(), DIRECTORY_SEPARATOR . '.');
    }

    /**
     * Create a folder -- and all necessary parent folders.
     *
     * @param  integer  $mode  Directory permissions to set for folders created. 0755 by default.
     *
     * @return  static
     *
     * @throws  FilesystemException
     * @since   2.0
     */
    public function mkdir(int $mode = 0755): static
    {
        static $nested = 0;

        // Check to make sure the path valid and clean
        $path = $this->getPathname();

        // Check if parent dir exists
        $parent = $this->getParent();

        if (!$parent->isDir()) {
            // Prevent infinite loops!
            $nested++;

            if ($nested > 20 || $parent === $path) {
                throw new FilesystemException(__METHOD__ . ': Infinite loop detected');
            }

            // Create the parent directory
            try {
                $parent->mkdir($mode);
            } catch (\Throwable $e) {
                $nested--;

                throw new FilesystemException($e->getMessage(), $e->getCode(), $e);
            }

            // OK, parent directory has been created
            $nested--;
        }

        // Check if dir already exists
        if ($this->isDir()) {
            return $this;
        }

        // First set umask
        $origmask = @umask(0);

        try {
            if (!mkdir($dir = $this->getPathname(), $mode) && !is_dir($dir)) {
                throw new FilesystemException('Unable to create dir of: ' . $dir);
            }
        } finally {
            @umask($origmask);
        }

        return $this;
    }

    /**
     * copy
     *
     * @param  string|\SplFileInfo  $dest
     * @param  bool                 $force
     *
     * @return  static
     */
    public function copyTo(\SplFileInfo|string $dest, bool $force = false): static
    {
        $dest = static::wrap($dest);

        if ($this->isDir()) {
            $this->copyFolderTo($dest, $force);
        } elseif ($this->isFile()) {
            $this->copyFileTo($dest, $force);
        } else {
            throw new FilesystemException('Trying to copy a non-exists path: ' . $this->getPathname());
        }

        return $dest;
    }

    /**
     * copyFolder
     *
     * @param  FileObject  $dest
     * @param  bool        $force
     *
     * @return  bool
     */
    private function copyFolderTo(FileObject $dest, bool $force = false): bool
    {
        // Eliminate trailing directory separators, if any
        $src = $this->getPathname();

        if ($dest->exists() && !$force) {
            throw new FileNotFoundException(
                sprintf(
                    'Destination folder exists: %s',
                    $dest
                )
            );
        }

        // Make sure the destination exists
        if (!$dest->mkdir()) {
            throw new FilesystemException(
                sprintf(
                    'Cannot create destination folder: %s',
                    $dest
                )
            );
        }

        // Walk through the directory copying files and recursing into folders.
        /** @var FileObject $file */
        foreach ($this->items(true) as $file) {
            $rFile = $file->getRelativePathname();

            $srcFile  = static::wrap($src . '/' . $rFile);
            $destFile = static::wrap($dest . '/' . $rFile);

            if ($srcFile->isDir()) {
                $destFile->mkdir();
            } elseif ($srcFile->isFile()) {
                $srcFile->copyFileTo($destFile, $force);
            }
        }

        return true;
    }

    /**
     * Copies a file
     *
     * @param  FileObject  $dest
     * @param  bool        $force
     *
     * @return  boolean  True on success
     *
     * @throws Exception\FilesystemException
     * @throws \UnexpectedValueException
     * @since   2.0
     */
    private function copyFileTo(FileObject $dest, bool $force = false): bool
    {
        // Check src path
        if (!$this->isReadable()) {
            throw new \UnexpectedValueException(__METHOD__ . ': Cannot find or read file: ' . $this->getPathname());
        }

        // Check folder exists
        $dir = $dest->getParent();

        if (!$dir->isDir()) {
            $dir->mkdir();
        }

        // Check is a folder or file
        if ($dest->exists()) {
            if ($force) {
                $dest->delete();
            } else {
                throw new FilesystemException($dest . ' has exists, copy failed.');
            }
        }

        return copy($this->getPathname(), $dest->getPathname());
    }

    /**
     * Move file or dir, return new FileObject of new path.
     *
     * @param  string|FileObject  $dest
     * @param  bool               $force
     *
     * @return  static
     */
    public function moveTo(FileObject|string $dest, bool $force = false): static
    {
        $dest = static::wrap($dest);

        $src = $this->getPathname();

        // Check src path
        if (!$this->isReadable()) {
            throw new FilesystemException('Cannot find source file: ' . $dest);
        }

        // Delete first if exists
        if ($dest->exists()) {
            if ($force) {
                $dest->delete();
            } else {
                throw new FilesystemException('File: ' . $dest->getPathname() . ' exists, move failed.');
            }
        }

        $dir = $dest->getParent();

        if (!$dir->isDir()) {
            $dir->mkdir();
        }

        if (!@rename($src, $dest->getPathname())) {
            throw new FilesystemException(
                error_get_last()['message']
            );
        }

        return $dest;
    }

    /**
     * Read file content as string.
     *
     * @param  bool      $useIncludePath
     * @param  null      $context
     * @param  int       $offset
     * @param  int|null  $maxlen
     *
     * @return  StringObject
     */
    public function read(
        bool $useIncludePath = false,
        $context = null,
        int $offset = 0,
        ?int $maxlen = null
    ): StringObject {
        if (!$this->exists()) {
            throw new FileNotFoundException('Try to read from a non-exists file: ' . $this->getPathname());
        }

        try {
            if ($maxlen) {
                $content = file_get_contents($this->getPathname(), $useIncludePath, $context, $offset, $maxlen);
            } else {
                $content = file_get_contents($this->getPathname(), $useIncludePath, $context, $offset);
            }

            if ($content === false) {
                $error = error_get_last();

                throw new FilesystemException(
                    $error['message'],
                    $error['type']
                );
            }
        } catch (\Throwable $e) {
            throw new FilesystemException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return \Windwalker\str($content);
    }

    /**
     * readStream
     *
     * @param  string  $mode
     *
     * @return  StreamInterface
     */
    public function readStream(string $mode = Stream::MODE_READ_ONLY_FROM_BEGIN): StreamInterface
    {
        return $this->getStream($mode);
    }

    /**
     * Write contents to a file
     *
     * @param  string|StreamInterface  $buffer
     *
     * @return  static
     */
    public function write(StreamInterface|string $buffer): static
    {
        ArgumentsAssert::assert(
            is_stringable($buffer),
            '{caller} argument 1 should be string or stringable object, %s given.',
            $buffer
        );

        // If the destination directory doesn't exist we need to create it
        $this->getParent()->mkdir();

        $result = file_put_contents($this->getPathname(), (string) $buffer);

        if (!$result) {
            throw new FilesystemException(error_get_last()['message'] ?? 'Unknown error');
        }

        return $this;
    }

    /**
     * writeStream
     *
     * @param  string|resource|StreamInterface  $stream
     *
     * @return  static
     */
    public function writeStream(mixed $stream): static
    {
        if (!$stream instanceof StreamInterface) {
            $stream = new Stream($stream, Stream::MODE_READ_ONLY_FROM_BEGIN);
        }

        // If the destination directory doesn't exist we need to create it
        $this->getParent()->mkdir();

        StreamHelper::copy($stream, $dest = $this->getStream());

        $dest->close();

        return $this;
    }

    /**
     * delete
     *
     * @return  static
     */
    public function delete(): static
    {
        $path = $this->getPathname();

        if ($this->isDir()) {
            // Delete children files
            foreach ($this->files(true) as $file) {
                $file->delete();
            }

            // Delete children folders
            foreach ($this->folders(true) as $folder) {
                $folder->delete();
            }
        }

        // Try making the file writable first. If it's read-only, it can't be deleted
        // on Windows, even if the parent folder is writable
        @chmod($path, 0777);

        // In case of restricted permissions we zap it one way or the other
        // as long as the owner is either the webserver or the ftp
        if ($this->isDir()) {
            $result = @rmdir($path);
        } else {
            $result = @unlink($path);
        }

        if (!$result) {
            new FilesystemException(error_get_last()['message']);
        }

        return $this;
    }

    /**
     * files
     *
     * @param  bool  $recursive
     *
     * @return  FilesIterator|FileObject[]
     */
    public function files(bool $recursive = false): FilesIterator
    {
        return FilesIterator::create($this->getPathname(), $recursive)
            ->filter(
                static function (FileObject $file) {
                    return $file->isFile();
                }
            );
    }

    /**
     * folders
     *
     * @param  bool  $recursive
     *
     * @return  FilesIterator|FileObject[]
     */
    public function folders(bool $recursive = false): FilesIterator
    {
        return FilesIterator::create($this->getPathname(), $recursive)
            ->filter(
                static function (FileObject $file) {
                    return $file->isDir();
                }
            );
    }

    /**
     * items
     *
     * @param  bool  $recursive
     *
     * @return  FilesIterator|FileObject[]
     */
    public function items($recursive = false): FilesIterator
    {
        return FilesIterator::create($this->getPathname(), $recursive);
    }

    /**
     * exists
     *
     * @param  int  $sensitive
     *
     * @return  bool
     */
    public function exists(int $sensitive = Path::CASE_OS_DEFAULT): bool
    {
        return Path::exists($this->getPathname(), $sensitive);
    }

    /**
     * getParent
     *
     * @param  int  $levels
     *
     * @return  static
     */
    public function getParent(int $levels = 1): static
    {
        return static::wrap(dirname($this->getPathname(), $levels));
    }

    /**
     * getStream
     *
     * @param  string  $mode
     *
     * @return  StreamInterface
     */
    public function getStream(string $mode = Stream::MODE_READ_WRITE_FROM_BEGIN): StreamInterface
    {
        if (!$this->exists()) {
            $this->touch();
        }

        return new Stream($this->getPathname(), $mode);
    }

    /**
     * touch
     *
     * @param  int|null  $time
     * @param  int|null  $atime
     *
     * @return  static
     */
    public function touch(?int $time = null, ?int $atime = null): static
    {
        touch($this->getPathname(), ...func_get_args());

        return $this;
    }

    /**
     * Append path and return a new instance.
     *
     * @param  string  $path
     *
     * @return  static
     */
    public function appendPath(string $path): static
    {
        $newPath = $this->getPathname() . $path;

        return static::wrap($newPath);
    }

    /**
     * Prepend path and return a new instance.
     *
     * @param  string  $path
     *
     * @return  static
     */
    public function prependPath(string $path): static
    {
        $newPath = $path . $this->getPathname();

        return static::wrap($newPath);
    }

    /**
     * Is this path a subdir or child of given path?
     *
     * @param  string|\SplFileInfo  $parent  Given path to detect.
     *
     * @return  boolean  Is subdir or not.
     */
    public function isChildOf(\SplFileInfo|string $parent): bool
    {
        $self = Path::normalize($this->getPathname());

        $parent = Path::normalize(static::unwrap($parent));

        // Path is self
        if ($self === $parent) {
            return false;
        }

        // Path is parent
        if (str_starts_with($self, $parent)) {
            return true;
        }

        return false;
    }

    /**
     * doAsync
     *
     * @param  string  $name
     * @param  array   $args
     *
     * @return  Promise
     */
    protected static function doAsync(string $name, array $args = []): Promise
    {
        return new Promise(
            function ($resolve) use ($name, $args) {
                $resolve(static::$name(...$args));
            }
        );
    }

    /**
     * Method to get property Root
     *
     * @return string|null
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getRoot(): ?string
    {
        return $this->root;
    }

    /**
     * Method to set property root
     *
     * @param  string|null  $root
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setRoot(?string $root): static
    {
        $this->root = $root;

        return $this;
    }

    public static function __callStatic(string $name, $args): Promise
    {
        $allows = [
            'read',
            'readStream',
            'write',
            'writeStream',
            'mkdir',
            'copyTo',
            'moveTo',
            'delete',
            'files',
            'folders',
            'items',
            'getStream',
        ];

        if (
            str_contains($name, 'Async')
            && in_array($method = substr($name, 0, -5), $allows, true)
        ) {
            return static::doAsync($method, $args);
        }

        throw new \BadMethodCallException(sprintf('Method %s::%s not exists.', static::class, $name));
    }
}
