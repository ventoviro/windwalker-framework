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

/**
 * The FilesystemTest class.
 */
class FilesystemTest extends AbstractVfsTestCase
{
    use FilesystemTestTrait;

    /**
     * @var Filesystem
     */
    protected $instance;

    public function testItemsFirst(): void
    {
        $fs = new Filesystem();
        $files = $fs::files('vfs://root/files', true);

        self::assertPathEquals('vfs://root/files/folder1/level2/file3', $files->first()->getPathname());
    }

    /**
     * @see  Filesystem::get
     */
    public function testGet(): void
    {
        $fs = new Filesystem();
        $file = $fs::get('vfs://root/files/folder2/file2.html');

        self::assertEquals('file2.html', (string) $file->read());
    }

    /**
     * testRead
     *
     * @return  void
     */
    public function testRead(): void
    {
        $fs = new Filesystem();
        $str = $fs::read('vfs://root/files/folder2/file2.html');

        self::assertEquals('file2.html', (string) $str);
    }

    /**
     * @see  Filesystem::delete
     */
    public function testDelete(): void
    {
        $fs = new Filesystem();

        $fs::delete(static::$baseDir . '');

        $this->assertDirectoryDoesNotExist(static::$baseDir . '');
        $this->assertFileDoesNotExist(static::$baseDir . '/folder1/level2/file3');

        restore_error_handler();

        // Delete non-exists folders
        try {
            $fs::delete(static::$baseDir . '/hello/no/exists');
        } catch (FilesystemException $e) {
            self::assertInstanceOf(FilesystemException::class, $e);
        }

        // Delete no-permissions folders
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $dir = __DIR__ . '/dest';
            $fs::mkdir($dir);
            chmod($dir, 0000);

            try {
                $fs::delete($dir);
            } catch (FilesystemException $e) {
                self::assertInstanceOf(FilesystemException::class, $e);
            }

            chmod($dir, 0777);
            $fs::delete($dir);
        }
    }

    /**
     * @see  Filesystem::mkdir
     */
    public function testMkdir(): void
    {
        $fs = new Filesystem();
        $dest = 'vfs://root/dest';

        $fs::mkdir($dest);

        static::assertDirectoryExists('vfs://root/dest');
    }

    /**
     * @see  Filesystem::copy
     */
    public function testCopy(): void
    {
        $fs = new Filesystem();

        $dest = 'vfs://root/dest';

        if (is_dir($dest)) {
            $fs::delete($dest);
        }

        $fs::copy(static::$baseDir, $dest);

        $this->assertDirectoryExists($dest);
        $this->assertFileExists($dest . '/folder1/level2/file3');
    }

    /**
     * @see  Filesystem::move
     */
    public function testMove(): void
    {
        $fs = new Filesystem();

        $dest = 'vfs://root/files2';

        if (is_dir($dest)) {
            $fs::delete($dest);
        }

        $fs::move(static::$baseDir, $dest);

        $this->assertDirectoryExists($dest);
        $this->assertFileExists($dest . '/folder1/level2/file3');

        $fs::delete($dest);
    }

    /**
     * @see  Filesystem::items
     */
    public function testItems(): void
    {
        $fs = new Filesystem();

        $items = $fs::items(static::$baseDir . '/folder1/level2', true);

        $this->assertEquals(
            static::cleanPaths([static::$baseDir . '/folder1/level2/file3']),
            static::cleanPaths($items)
        );

        // Recursive
        $items = $fs::items(static::$baseDir . '', true);

        $compare = static::getItemsRecursive();

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($items)
        );

        // Iterator
        $items = $fs::items(static::$baseDir, true);

        $this->assertInstanceOf(FilesIterator::class, $items);

        $items2 = Filesystem::toArray($items);

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($items2)
        );

        $items->rewind();

        $this->assertInstanceOf(SplFileInfo::class, $items->current());

        // list non-exists folder
        restore_error_handler();

        $this->expectException(FileNotFoundException::class);

        $items = $fs::items(__DIR__ . '/not/exists');
    }

    /**
     * @see  Filesystem::glob
     */
    public function testGlob(): void
    {
        $fs = new Filesystem();
        $files = $fs::glob('vfs://root/**/*');

        static::assertEquals(
            static::cleanPaths($fs::items('vfs://root', true)),
            static::cleanPaths($files->toArray())
        );
    }

    /**
     * @see  Filesystem::globAll
     */
    public function testGlobAll(): void
    {
        $fs = new Filesystem();

        $iter = $fs::globAll(
            [
                'vfs://root/files/folder1/**/*',
                'vfs://root/files/folder2/**/*',
            ]
        );

        self::assertEquals(
            static::cleanPaths(
                [
                    'vfs://root/files/folder1/level2',
                    'vfs://root/files/folder1/level2/file3',
                    'vfs://root/files/folder1/path1',
                    'vfs://root/files/folder2/file2.html',
                ]
            ),
            static::cleanPaths(
                $iter->toArray()
            )
        );
    }

    /**
     * @see  Filesystem::folders
     */
    public function testFolders(): void
    {
        $fs = new Filesystem();

        $folders = $fs::folders(static::$baseDir . '/folder1', true);

        $this->assertEquals(
            static::cleanPaths([static::$baseDir . '/folder1/level2']),
            static::cleanPaths($folders)
        );

        // Recursive
        $folders = $fs::folders(static::$baseDir, true);

        $compare = static::getFoldersRecursive('dest');

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($folders)
        );

        // Iterator
        $folders = $fs::folders(static::$baseDir, true);

        $this->assertInstanceOf(FilesIterator::class, $folders);

        $folders2 = Filesystem::toArray($folders);

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($folders2)
        );

        $folders = $fs::folders(static::$baseDir, true);

        $this->assertInstanceOf(FileObject::class, $folders->current());
    }

    /**
     * @see  Filesystem::files
     */
    public function testFiles(): void
    {
        $fs = new Filesystem();

        $files = $fs::files('vfs://root/files/folder1/level2', true);

        $this->assertEquals(
            static::cleanPaths(['vfs://root/files/folder1/level2/file3']),
            static::cleanPaths($files)
        );

        // Recursive
        $files = $fs::files(static::$baseDir, true);

        $compare = static::getFilesRecursive();

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($files)
        );

        // Iterator
        $files = $fs::files(static::$baseDir, true);

        $this->assertInstanceOf(FilesIterator::class, $files);

        $files2 = Filesystem::toArray($files);

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($files2)
        );

        $files->rewind();

        $this->assertInstanceOf(FileObject::class, $files->current());
    }
}
