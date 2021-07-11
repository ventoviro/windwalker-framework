<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filesystem\Test;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Windwalker\Filesystem\PathCollection;

use function Windwalker\fs;

/**
 * The PathCollectionTest class.
 */
class PathCollectionTest extends AbstractVfsTestCase
{
    /**
     * @var PathCollection
     */
    protected $instance;

    /**
     * @see  PathCollection::isChild
     */
    public function testIsChild(): void
    {
        $p = new PathCollection(
            [
                'vfs://foo/bar/yoo',
                'vfs://foo/goo',
            ]
        );

        self::assertTrue($p->isChild('vfs://foo/goo/joo'));
        self::assertFalse($p->isChild('vfs://foo/tu'));
    }

    /**
     * @see  PathCollection::addPaths
     */
    public function testAddPaths(): void
    {
        $p = new PathCollection(
            [
                'vfs://foo/bar/yoo',
            ]
        );

        $p2 = $p->addPaths(
            [
                'vfs://flower/sakura',
                'vfs://foo/goo',
            ]
        );

        self::assertEquals(
            [
                'vfs://foo/bar/yoo',
                'vfs://flower/sakura',
                'vfs://foo/goo',
            ],
            $p2->toArray()
        );
        self::assertNotSame($p, $p2);
    }

    /**
     * @see  PathCollection::add
     */
    public function testAdd(): void
    {
        $p = new PathCollection(
            [
                'vfs://foo/bar/yoo',
            ]
        );

        $p2 = $p->add('vfs://flower/sakura');

        self::assertEquals(
            [
                'vfs://foo/bar/yoo',
                'vfs://flower/sakura',
            ],
            $p2->toArray()
        );
        self::assertNotSame($p, $p2);
    }

    /**
     * @see  PathCollection::appendAll
     */
    public function testAppendAll(): void
    {
        $p = new PathCollection(
            [
                'foo/bar/yoo',
                'flower/sakura',
                'foo/goo/joo',
            ]
        );

        $p2 = $p->appendAll('/../');

        self::assertEquals(
            [
                'foo/bar/yoo/..',
                'flower/sakura/..',
                'foo/goo/joo/..',
            ],
            $p2->toArray()
        );
        self::assertNotSame($p, $p2);
    }

    /**
     * @see  PathCollection::prependAll
     */
    public function testPrependAll(): void
    {
        $p = new PathCollection(
            [
                'foo/bar/yoo',
                'flower/sakura',
                'foo/goo/joo',
            ]
        );

        $p2 = $p->prependAll('vfs://');

        self::assertEquals(
            [
                'vfs://foo/bar/yoo',
                'vfs://flower/sakura',
                'vfs://foo/goo/joo',
            ],
            $p2->toArray()
        );
        self::assertNotSame($p, $p2);
    }

    /**
     * @see  PathCollection::getPaths
     */
    public function testGetPaths(): void
    {
        $p = new PathCollection(
            [
                'foo/bar/yoo',
                'flower/sakura',
                'foo/goo/joo',
            ]
        );

        self::assertEquals(
            [
                'foo/bar/yoo',
                'flower/sakura',
                'foo/goo/joo',
            ],
            array_map('strval', $p->getPaths())
        );
    }

    /**
     * @see  PathCollection::withPaths
     */
    public function testWithPaths(): void
    {
        $p = new PathCollection(
            [
                'vfs://foo/bar/yoo',
            ]
        );

        $p2 = $p->withPaths(
            [
                'vfs://flower/sakura',
                'vfs://foo/goo',
            ]
        );

        self::assertEquals(
            [
                'vfs://flower/sakura',
                'vfs://foo/goo',
            ],
            $p2->toArray()
        );
        self::assertNotSame($p, $p2);
    }

    /**
     * @see  PathCollection::items
     */
    public function testItems(): void
    {
        $this->setUpNestedFiles();

        $p = new PathCollection(
            [
                'vfs://root/folder1',
                'vfs://root/folder2',
            ]
        );

        self::assertEquals(
            static::cleanPaths(
                array_merge(
                    fs('vfs://root/folder1')->items(true)->toArray(),
                    fs('vfs://root/folder2')->items(true)->toArray()
                )
            ),
            static::cleanPaths($p->items(true)->toArray())
        );
    }

    /**
     * @see  PathCollection::files
     */
    public function testFiles(): void
    {
        $this->setUpNestedFiles();

        $p = new PathCollection(
            [
                'vfs://root/folder1',
                'vfs://root/folder2',
            ]
        );

        self::assertEquals(
            static::cleanPaths(
                array_merge(
                    fs('vfs://root/folder1')->files(true)->toArray(),
                    fs('vfs://root/folder2')->files(true)->toArray()
                )
            ),
            static::cleanPaths($p->files(true)->toArray())
        );
    }

    /**
     * @see  PathCollection::folders
     */
    public function testFolders(): void
    {
        $this->setUpNestedFiles();

        $p = new PathCollection(
            [
                'vfs://root/folder1',
                'vfs://root/folder2',
            ]
        );

        self::assertEquals(
            static::cleanPaths(
                array_merge(
                    fs('vfs://root/folder1')->folders(true)->toArray(),
                    fs('vfs://root/folder2')->folders(true)->toArray()
                )
            ),
            static::cleanPaths($p->folders(true)->toArray())
        );
    }

    /**
     * @see  PathCollection::getPath
     */
    public function testGetPath(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  PathCollection::map
     */
    public function testMap(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUpNestedFiles(): vfsStreamDirectory
    {
        return vfsStream::setup(
            'root',
            null,
            [
                'folder1' => [
                    'sub1' => [
                        'file1.txt' => '',
                        'file2.txt' => '',
                        'file3.txt' => '',
                    ],
                    'sub2' => [
                        'file1.txt' => '',
                        'file2.txt' => '',
                        'file3.txt' => '',
                    ],
                ],
                'folder2' => [
                    'sub1' => [
                        'file1.txt' => '',
                        'file2.txt' => '',
                        'file3.txt' => '',
                    ],
                    'sub2' => [
                        'file1.txt' => '',
                        'file2.txt' => '',
                        'file3.txt' => '',
                    ],
                ],
            ]
        );
    }

    protected function setUp(): void
    {
        $this->instance = null;
    }

    protected function tearDown(): void
    {
    }
}
