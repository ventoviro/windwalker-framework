<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Relation\Strategy;

use Windwalker\Data\Collection;
use Windwalker\Database\Schema\Schema;
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\StubLocation;
use Windwalker\ORM\Test\Entity\StubLocationData;
use Windwalker\ORM\Test\Entity\StubRose;
use Windwalker\ORM\Test\Entity\StubSakura;

/**
 * The OneToOneTest class.
 */
class OneToManyTest extends AbstractORMTestCase
{
    public function testLoad()
    {
        $mapper = $this->createTestMapper();

        /** @var StubLocation $item */
        $item = $mapper->findOne(3);

        $sakuras = $item->getSakuras();
        $roses = $item->getRoses();

        self::assertEquals([11, 12, 13, 14, 15], $sakuras->all(Collection::class)->column('id')->dump());
        self::assertEquals([11, 12, 13, 14, 15], $roses->all(Collection::class)->column('id')->dump());
    }

    public function testJsonSerialize()
    {
        $mapper = $this->createTestMapper();

        /** @var StubLocation $item */
        $item = $mapper->findOne(1);

        $encoded = json_encode($item);

        self::assertEquals(
            [],
            json_decode($encoded, true)['sakuras'],
        );
    }

    public function testCreate()
    {
        $mapper = $this->createTestMapper();

        $location = new StubLocation();
        $location->setTitle('Location Create 1');
        $location->setState(1);

        $sakuras = $location->getSakuras();

        $sakura1 = new StubSakura();
        $sakura1->setTitle('Sakura Create 1');
        $sakura1->setState(1);

        $sakuras->attach($sakura1);

        $sakura2 = new StubSakura();
        $sakura2->setTitle('Sakura Create 2');
        $sakura2->setState(1);

        $sakuras->attach($sakura2);

        $roses = $location->getRoses();

        $rose1 = new StubRose();
        $rose1->setTitle('Rose Create 1');
        $rose1->setState(1);
        $rose1->setNo('R20001');

        $rose2 = new StubRose();
        $rose2->setTitle('Rose Create 2');
        $rose2->setState(1);
        $rose2->setNo('R20002');

        $roses->attach(compact('rose1', 'rose2'));

        $mapper->createOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(['title' => 'Location Create 1']);

        self::assertEquals(
            ['Rose Create 1', 'Rose Create 2'],
            $newLocation->getRoses()
                ->all(Collection::class)
                ->column('title')
                ->dump()
        );
    }

    public function testUpdateAttachAndDetach()
    {
        $mapper   = $this->createTestMapper();
        /** @var StubLocation $location */
        $location = $mapper->findOne(1);
        $location->setState(2);

        $sakuras = $location->getSakuras();

        $sakuras->detach($sakuras->all()[0]);

        $sakura = (new StubSakura())
            ->setTitle('New Sakura 1')
            ->setState(1);

        $sakuras->attach($sakura);

        $mapper->updateOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(1);

        $newSakuras = $newLocation->getSakuras()->all(Collection::class);

        self::assertEquals(2, $newLocation->getState());
        self::assertEquals(
            [2, 3, 4, 5, 28],
            $newSakuras->column('id')->dump()
        );

        // Id 1 location_no should be empty
        self::assertEmpty(
            self::$orm->findOne(StubSakura::class, 1)->getLocationNo()
        );
    }

    public function testUpdateSync(): void
    {
        $mapper   = $this->createTestMapper();
        /** @var StubLocation $location */
        $location = $mapper->findOne(2);

        $sakurasCollection = $location->getSakuras();
        $sakuras = $sakurasCollection->all(StubSakura::class);

        $sakuras[0] = (new StubSakura())
            ->setTitle('Create Sakura 2')
            ->setState(1);

        $sakurasCollection->sync($sakuras);

        $mapper->updateOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(2);

        $newSakuras = $newLocation->getSakuras()->all(Collection::class);

        self::assertEquals(
            [7, 8, 9, 10, 29],
            $newSakuras->column('id')->dump()
        );
        // Id 1 location_no should be empty
        self::assertEmpty(
            self::$orm->findOne(StubSakura::class, 6)->getLocationNo()
        );
    }

    public function testUpdateCascade()
    {
        $mapper   = $this->createTestMapper();
        /** @var StubLocation $location */
        $location = $mapper->findOne(3);
        $location->setNo($location->getNo() . '-2');

        $sakuras = $location->getSakuras();

        $sakuras->detach($sakuras->all()[0]);

        $sakura = (new StubSakura())
            ->setTitle('New Sakura 4')
            ->setState(1);

        $sakuras->attach($sakura);

        $mapper->updateOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(3);

        $newSakuras = $newLocation->getSakuras()->all(Collection::class);

        self::assertEquals(
            [12, 13, 14, 15, 30],
            $newSakuras->column('id')->dump()
        );

        self::assertEquals(
            ['L00003-2'],
            $newSakuras->column('location_no')->unique()->dump()
        );

        // The detached fk should be empty
        self::assertEquals(
            '',
            self::$orm->from(StubSakura::class)
                ->where('id', 11)
                ->get()
                ->location_no
        );
    }


    public function testUpdateSyncCascade()
    {
        $mapper   = $this->createTestMapper();
        /** @var StubLocation $location */
        $location = $mapper->findOne(3);
        $location->setNo($location->getNo() . '-2');

        $sakurasCollection = $location->getSakuras();
        $sakuras = $sakurasCollection->all();

        $sakuras[0] = (new StubSakura())
            ->setTitle('Create Sakura 2')
            ->setState(1);

        $sakurasCollection->sync($sakuras);

        $mapper->updateOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(3);

        $newSakuras = $newLocation->getSakuras()->all(Collection::class);

        self::assertEquals(
            [13, 14, 15, 30, 31],
            $newSakuras->column('id')->dump()
        );

        self::assertEquals(
            ['L00003-2-2'],
            $newSakuras->column('location_no')->unique()->dump()
        );

        // The detached fk should be empty
        self::assertEquals(
            '',
            self::$orm->from(StubSakura::class)
                ->where('id', 12)
                ->get()
                ->location_no
        );
    }

    public function testUpdateWithoutInitCollection()
    {
        $mapper   = $this->createTestMapper();
        /** @var StubLocation $location */
        $location = $mapper->findOne(3);
        $location->setNo('L00003-3');

        $mapper->updateOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(3);

        $newSakuras = $newLocation->getSakuras()->all(Collection::class);

        self::assertEquals(
            [13, 14, 15, 30, 31],
            $newSakuras->column('id')->dump()
        );

        self::assertEquals(
            ['L00003-3'],
            $newSakuras->column('location_no')->unique()->dump()
        );
    }

    public function testUpdateNoAction()
    {
        $mapper   = $this->createTestMapper(Action::NO_ACTION);
        /** @var StubLocation $location */
        $location = $mapper->findOne(3);
        $location->setNo('L00003-4');

        $mapper->updateOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(3);

        $newSakuras = $newLocation->getSakuras()->all(Collection::class);

        self::assertCount(0, $newSakuras);

        // The detached fk should be empty
        self::assertEquals(
            'L00003-3',
            self::$orm->from(StubSakura::class)
                ->where('id', 13)
                ->get()
                ->location_no
        );
    }

    public function testUpdateSetNull()
    {
        $mapper = $this->createTestMapper(Action::SET_NULL);

        /** @var StubLocation $location */
        $location = $mapper->findOne(4);
        $location->setNo('L00004-2');

        $mapper->saveOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(4);

        $newSakuras = $newLocation->getSakuras()->all(Collection::class);

        self::assertCount(0, $newSakuras);

        // The detached fk should be empty
        self::assertEquals(
            '',
            self::$orm->from(StubSakura::class)
                ->where('id', 16)
                ->get()
                ->location_no
        );
    }

    public function testDelete()
    {
        $mapper = $this->createTestMapper();

        /** @var StubLocation $location */
        $location = $mapper->findOne(1);

        $mapper->delete($location);

        $sakuras = $location->getSakuras()->all();

        self::assertCount(0, $sakuras);
        self::assertNull(
            self::$orm->findOne(StubSakura::class, 2)
        );
        self::assertCount(
            0,
            self::$orm->from(StubSakura::class)
                ->where('location_no', $location->getNo())
                ->all()
        );
    }

    public function testDeleteNoAction()
    {
        $mapper = $this->createTestMapper(Action::NO_ACTION, Action::NO_ACTION);

        /** @var StubLocation $location */
        $location = $mapper->findOne(2);

        $mapper->delete($location);

        self::assertEquals(
            [7, 8, 9, 10, 29],
            self::$orm->from(StubSakura::class)
                ->where('location_no', $location->getNo())
                ->all()
                ->column('id')
                ->dump()
        );
    }

    public function testDeleteSetNull()
    {
        $mapper = $this->createTestMapper(Action::CASCADE, Action::SET_NULL);

        /** @var StubLocation $location */
        $location = $mapper->findOne(5);

        $ids = $location->getSakuras()
            ->all(Collection::class)
            ->column('id')
            ->dump();

        $location->clearRelations();

        $mapper->delete($location);

        self::assertEquals(
            [null, null, null, null, null],
            self::$orm->from(StubSakura::class)
                ->where('id', $ids)
                ->all()
                ->column('location_no')
                ->dump()
        );
    }

    public function createTestMapper(
        string $onUpdate = Action::CASCADE,
        string $onDelete = Action::CASCADE,
        bool $flush = false
    ) {
        $mapper = self::$orm->mapper(StubLocation::class);
        $rm = $mapper->getMetadata()
            ->getRelationManager();

        $rm->oneToMany('sakuras')
            ->target(StubSakura::class, ['no' => 'location_no'])
            ->flush($flush)
            ->onUpdate($onUpdate)
            ->onDelete($onDelete);

        $rm->oneToMany('roses')
            ->target(StubRose::class, ['no' => 'location_no'])
            ->flush($flush)
            ->onUpdate($onUpdate)
            ->onDelete($onDelete);

        return $mapper;
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/../../Stub/relations.sql');
    }
}
