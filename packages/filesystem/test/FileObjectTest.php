<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filesystem\Test;

use SplFileInfo;
use Windwalker\Filesystem\Exception\FileNotFoundException;
use Windwalker\Filesystem\Exception\FilesystemException;
use Windwalker\Filesystem\FileObject;
use Windwalker\Filesystem\Filesystem;
use Windwalker\Filesystem\Iterator\FilesIterator;
use Windwalker\Stream\StringStream;

use function Windwalker\fs;

/**
 * The FilesystemTest class.
 */
class FileObjectTest extends AbstractVfsTestCase
{
    use FilesystemTestTrait;

    /**
     * @var Filesystem
     */
    protected Filesystem $instance;

    public function testItemsFirst(): void
    {
        $fs = new FileObject('vfs://root/files');
        $files = $fs->files(true);

        self::assertPathEquals('vfs://root/files/folder1/level2/file3', $files->first()->getPathname());
    }

    /**
     * testRead
     *
     * @return  void
     */
    public function testRead(): void
    {
        $str = (new FileObject('vfs://root/files/folder2/file2.html'))->read();

        self::assertEquals('file2.html', (string) $str);
    }

    public function testReadStream(): void
    {
        $stream = (new FileObject('vfs://root/files/folder2/file2.html'))->readStream();

        self::assertEquals('file2.html', (string) $stream);
    }

    public function testWrite(): void
    {
        (new FileObject('vfs://root/files/folder2/foo/bar/new-file.txt'))->write('Hello');

        self::assertEquals('Hello', file_get_contents('vfs://root/files/folder2/foo/bar/new-file.txt'));
    }

    public function testWriteStream(): void
    {
        $stream = new StringStream('Hello');

        (new FileObject('vfs://root/files/folder2/foo/bar/new-file.txt'))->writeStream($stream);

        self::assertEquals('Hello', file_get_contents('vfs://root/files/folder2/foo/bar/new-file.txt'));
    }

    /**
     * @see  FileObject::delete
     */
    public function testDelete(): void
    {
        $file = new FileObject(static::$baseDir . '');
        $file->delete();

        self::assertDirectoryDoesNotExist(static::$baseDir . '');
        self::assertFileDoesNotExist(static::$baseDir . '/folder1/level2/file3');

        restore_error_handler();

        // Delete non-exists folders
        try {
            (new FileObject(static::$baseDir . '/hello/no/exists'))->delete();
        } catch (FilesystemException $e) {
            self::assertInstanceOf(FilesystemException::class, $e);
        }

        // Delete no-permissions folders
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $dir = new FileObject(__DIR__ . '/dest');
            $dir->mkdir();
            chmod($dir->getPathname(), 0000);

            try {
                $dir->delete();
            } catch (FilesystemException $e) {
                self::assertInstanceOf(FilesystemException::class, $e);
            }

            chmod($dir->getPathname(), 0777);
            $dir->delete();
        }
    }

    /**
     * @see  FileObject::mkdir
     */
    public function testMkdir(): void
    {
        $dest = fs('vfs://root/dest/foo/bar');

        $dest->mkdir();

        static::assertDirectoryExists('vfs://root/dest/foo/bar');
    }

    /**
     * @see  FileObject::copy
     */
    public function testCopyTo(): void
    {
        $dest = new FileObject('vfs://root/dest');

        if ($dest->isDir()) {
            $dest->delete();
        }

        (new FileObject(static::$baseDir))->copyTo($dest);

        $this->assertDirectoryExists($dest->getPathname());
        $this->assertFileExists($dest . '/folder1/level2/file3');
    }

    /**
     * @see  FileObject::move
     */
    public function testMoveTo(): void
    {
        $dest = new FileObject('vfs://root/files2');

        if ($dest->isDir()) {
            $dest->delete();
        }

        (new FileObject(static::$baseDir))->moveTo($dest);

        $this->assertDirectoryExists($dest->getPathname());
        $this->assertFileExists($dest . '/folder1/level2/file3');

        $dest->delete();
    }

    /**
     * @see  FileObject::items
     */
    public function testItems(): void
    {
        $items = (new FileObject(static::$baseDir . '/folder1/level2'))->items(true);

        $this->assertEquals(
            static::cleanPaths([static::$baseDir . '/folder1/level2/file3']),
            static::cleanPaths($items)
        );

        // Recursive
        $items = (new FileObject(static::$baseDir . ''))->items(true);

        $compare = static::getItemsRecursive();

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($items)
        );

        // Iterator
        $items = (new FileObject(static::$baseDir))->items(true);

        $this->assertInstanceOf(FilesIterator::class, $items);

        $items2 = $items->toArray();

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($items2)
        );

        $items->rewind();

        $this->assertInstanceOf(SplFileInfo::class, $items->current());

        // list non-exists folder
        restore_error_handler();

        $this->expectException(FileNotFoundException::class);

        $items = (new FileObject(__DIR__ . '/not/exists'))->items();
    }

    /**
     * @see  FileObject::folders
     */
    public function testFolders(): void
    {
        $folders = (new FileObject(static::$baseDir . '/folder1'))->folders(true);

        $this->assertEquals(
            static::cleanPaths([static::$baseDir . '/folder1/level2']),
            static::cleanPaths($folders)
        );

        // Recursive
        $folders = (new FileObject(static::$baseDir))->folders(true);

        $compare = static::getFoldersRecursive();

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($folders)
        );

        // Iterator
        $folders = (new FileObject(static::$baseDir))->folders(true);

        $this->assertInstanceOf(FilesIterator::class, $folders);

        $folders2 = $folders->toArray();

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($folders2)
        );

        $folders = (new FileObject(static::$baseDir))->folders(true);

        $this->assertInstanceOf(FileObject::class, $folders->current());
    }

    /**
     * @see  FileObject::files
     */
    public function testFiles(): void
    {
        $files = (new FileObject('vfs://root/files/folder1/level2'))->files(true);

        $this->assertEquals(
            static::cleanPaths(['vfs://root/files/folder1/level2/file3']),
            static::cleanPaths($files)
        );

        // Recursive
        $files = (new FileObject(static::$baseDir))->files(true);

        $compare = static::getFilesRecursive();

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($files)
        );

        // Iterator
        $files = (new FileObject(static::$baseDir))->files(true);

        $this->assertInstanceOf(FilesIterator::class, $files);

        $files2 = $files->toArray();

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($files2)
        );

        $files->rewind();

        $this->assertInstanceOf(FileObject::class, $files->current());
    }

    public function testWrapIfNotNull(): void
    {
        self::assertNull(FileObject::wrapIfNotNull(null));
    }

    public function testGetRelativePathname()
    {
        $file = fs('vfs://root/files\\folder1/foo/bar', 'vfs://root\\files');

        self::assertPathEquals('folder1/foo/bar', $file->getRelativePathname());
        self::assertPathEquals('foo/bar', $file->getRelativePathname('vfs://root\\files/folder1'));

        $file = fs('vfs://root/files\\folder1/foo/bar', '');

        self::assertPathEquals('vfs://root/files/folder1/foo/bar', $file->getRelativePathname());
        self::assertPathEquals(
            'bar',
            $file->getRelativePathname('vfs://root/files\\folder1/foo')
        );
        self::assertPathEquals(
            'vfs://root/files/folder1/foo/bar',
            $file->getRelativePathname('vfs://root/files\\folder1/foo/yoo')
        );
    }

    public function testAppendPath()
    {
        $file = fs('vfs://root')->appendPath('/files/folder1');

        self::assertPathEquals('vfs://root\files/folder1', $file->getPathname());
    }

    public function testPrependPath()
    {
        $file = fs('root/files/folder1')->prependPath('vfs://');

        self::assertPathEquals('vfs://root\files/folder1', $file->getPathname());
    }
}
