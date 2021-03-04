<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\ORM\Attributes\NestedSet;
use Windwalker\ORM\Exception\NestedHandleException;
use Windwalker\ORM\Nested\NestedEntityInterface;
use Windwalker\ORM\Nested\Position;
use Windwalker\ORM\NestedSetMapper;
use Windwalker\ORM\Test\Entity\StubNestedSet;

/**
 * The NestedSetMapperTest class.
 */
class NestedSetMapperTest extends AbstractORMTestCase
{
    protected ?NestedSetMapper $instance;

    public function testRoot()
    {
        $items = iterator_to_array($this->instance->findList([]));

        self::assertEquals(
            'root',
            $items[0]->getTitle()
        );
    }

    public function testCheckParentIdEmpty()
    {
        $this->expectException(NestedHandleException::class);
        $this->expectExceptionMessage('Invalid parent_id: 0');

        $child = new StubNestedSet();
        $child->setLft(2);

        $this->instance->saveOne($child);
    }

    public function testCheckParentIdNotExists()
    {
        $this->expectExceptionMessage('Reference ID 999 not found.');

        $child = new StubNestedSet();

        $this->instance->setPosition($child, 999, Position::FIRST_CHILD);

        $this->instance->createOne($child);
    }

    public function testPostionAndSave()
    {
        $child = new StubNestedSet();
        $child->setTitle('Flower');
        $child->setAlias('flower');

        $this->instance->setPosition($child, 1, Position::FIRST_CHILD);
        $this->instance->saveOne($child);

        $child = new StubNestedSet();
        $child->setTitle('Sakura');
        $child->setAlias('sakura');

        $this->instance->setPosition($child, 2, Position::FIRST_CHILD);
        $this->instance->saveOne($child);
        $this->instance->rebuildPath($child);

        // First child
        $child = new StubNestedSet();
        $child->setTitle('Olive');
        $child->setAlias('olive');

        $this->instance->setPosition($child, 2, Position::FIRST_CHILD);
        $ent = $this->instance->saveOne($child);
        $this->instance->rebuildPath($child);

        self::assertEquals(
            [2, 3],
            [
                $ent->getLft(),
                $ent->getRgt()
            ]
        );

        // Last child
        $child = new StubNestedSet();
        $child->setTitle('Sunflower');
        $child->setAlias('sunflower');

        $this->instance->setPosition($child, 2, Position::LAST_CHILD);
        /** @var NestedEntityInterface $ent */
        $ent = $this->instance->saveOne($child);
        $this->instance->rebuildPath($child);

        self::assertEquals(
            [6, 7],
            [
                $ent->getLft(),
                $ent->getRgt()
            ]
        );
    }

    /**
     * @see  NestedSetMapper::move
     */
    public function testMove(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  NestedSetMapper::getRoot
     */
    public function testGetRoot(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  NestedSetMapper::getAncestors
     */
    public function testGetAncestors(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  NestedSetMapper::postProcessDelete
     */
    public function testPostProcessDelete(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  NestedSetMapper::rebuild
     */
    public function testRebuild(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  NestedSetMapper::emitEvent
     */
    public function testEmitEvent(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  NestedSetMapper::getTree
     */
    public function testGetTree(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  NestedSetMapper::setPosition
     */
    public function testSetPosition(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  NestedSetMapper::isLeaf
     */
    public function testIsLeaf(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  NestedSetMapper::createRoot
     */
    public function testCreateRoot(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  NestedSetMapper::rebuildPath
     */
    public function testRebuildPath(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  NestedSetMapper::moveByReference
     */
    public function testMoveByReference(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  NestedSetMapper::isPathable
     */
    public function testIsPathable(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        /** @var NestedSetMapper $mapper */
        $mapper = self::$orm->mapper(StubNestedSet::class);

        $this->instance = $mapper;
    }

    protected function tearDown(): void
    {
    }

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /** @var NestedSetMapper $mapper */
        $mapper = self::$orm->mapper(StubNestedSet::class);
        $mapper->createRoot(
            [
                'title' => 'root',
                'access' => 1
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/Stub/nested.sql');
    }
}
