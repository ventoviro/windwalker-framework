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
    // public function testReset()
    // {
    //     self::$db->getTable(Location::class)->update(function (Schema $schema) {
    //         $schema->varchar('no')->position('AFTER', 'id');
    //     });
    //
    //     foreach (self::$orm->from(Location::class) as $item) {
    //         $item->no = 'L' . str_pad($item->id, 5, '0', STR_PAD_LEFT);
    //
    //         self::$orm->updateOne(Location::class, $item);
    //     }
    //
    //     self::$db->getTable(LocationData::class)->update(
    //         function (Schema $schema) {
    //             $schema->varchar('location_no')->position('AFTER', 'location_id');
    //             $schema->addIndex('location_no');
    //         }
    //     );
    //
    //     $items = self::$db->select()->from(LocationData::class)->all();
    //
    //     foreach ($items as $item) {
    //         $item->location_no = self::$orm->findOne(Location::class, $item->location_id)->no;
    //
    //         self::$orm->updateOne(LocationData::class, $item);
    //     }
    //
    //     self::$db->getTable(StubSakura::class)->update(
    //         function (Schema $schema) {
    //             $schema->varchar('no')->position('AFTER', 'id');
    //             $schema->varchar('location_no')->position('AFTER', 'location');
    //             $schema->varchar('rose_no')->position('AFTER', 'location_no');
    //             $schema->addIndex('no');
    //             $schema->addIndex('location_no');
    //         }
    //     );
    //
    //     self::$db->getTable(StubRose::class)->update(
    //         function (Schema $schema) {
    //             $schema->varchar('no')->position('AFTER', 'id');
    //             $schema->varchar('location_no')->position('AFTER', 'location');
    //             $schema->varchar('sakura_no')->position('AFTER', 'location_no');
    //             $schema->addIndex('no');
    //             $schema->addIndex('location_no');
    //         }
    //     );
    //
    //     $items = self::$db->select()->from(StubSakura::class)->all();
    //
    //     foreach ($items as $item) {
    //         $item->location_no = self::$orm->findOne(Location::class, $item->location)->no;
    //         $item->rose_no = self::$db->select()
    //             ->from('roses')
    //             ->where('id', $item->rose_id)
    //             ->get()
    //             ->no;
    //         $item->no = 'S' . str_pad($item->id, 5, '0', STR_PAD_LEFT);
    //
    //         self::$orm->updateOne(StubSakura::class, $item);
    //     }
    //
    //     $items = self::$db->select()->from(StubRose::class)->all();
    //
    //     foreach ($items as $item) {
    //         $item->location_no = self::$orm->findOne(Location::class, $item->location)->no;
    //         $item->sakura_no = self::$db->select()
    //             ->from('sakuras')
    //             ->where('id', $item->sakura_id)
    //             ->get()
    //             ->no;
    //         $item->no = 'R' . str_pad($item->id, 5, '0', STR_PAD_LEFT);
    //
    //         self::$orm->updateOne(StubRose::class, $item);
    //     }
    //
    //     self::$db->getTable('sakura_rose_maps')->update(
    //         function (Schema $schema) {
    //             $schema->varchar('sakura_no');
    //             $schema->varchar('rose_no');
    //         }
    //     );
    //
    //     $items = self::$db->select()->from('sakura_rose_maps')->all();
    //
    //     foreach ($items as $item) {
    //         $item->sakura_no = self::$db->select()
    //             ->from('sakuras')
    //             ->where('id', $item->sakura_id)
    //             ->get()
    //             ->no;
    //         $item->rose_no = self::$db->select()
    //             ->from('roses')
    //             ->where('id', $item->rose_id)
    //             ->get()
    //             ->no;
    //
    //         self::$db->getWriter()->updateOne(
    //             'sakura_rose_maps',
    //             $item,
    //             ['rose_id', 'sakura_id']
    //         );
    //     }
    // }

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

        $sakuras->add($sakura1);

        $sakura2 = new StubSakura();
        $sakura2->setTitle('Sakura Create 2');
        $sakura2->setState(1);

        $sakuras->add($sakura2);

        $roses = $location->getRoses();

        $rose1 = new StubRose();
        $rose1->setTitle('Rose Create 1');
        $rose1->setState(1);

        $rose2 = new StubRose();
        $rose2->setTitle('Rose Create 2');
        $rose2->setState(1);

        $roses->add(compact('rose1', 'rose2'));

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

    public function testUpdateAddRemove()
    {
        $mapper   = $this->createTestMapper();
        /** @var StubLocation $location */
        $location = $mapper->findOne(1);

        $location->setState(2);
        $location->getData()->setData('123');

        $mapper->updateOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(1);

        self::assertEquals(2, $newLocation->getState());
        self::assertEquals($location->getState(), $newLocation->getState());
        self::assertEquals(
            $newLocation->getData()->getData(),
            self::$orm->from(StubLocationData::class)
                ->where('id', 6)
                ->get()
                ->data
        );

        // Update Without child value
        /** @var StubLocation $location */
        $location = $mapper->findOne(1);

        $mapper->updateOne($location);

        self::assertEquals(
            '123',
            $location->getData()->getData()
        );
    }

    public function testUpdateNoAction()
    {
        $mapper   = $this->createTestMapper(Action::NO_ACTION);

        /** @var StubLocation $location */
        $location = $mapper->findOne(1);

        $location->setId(null);
        $location->setState(1);
        $location->getData()->setData('Gandalf');

        $mapper->saveOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(7);

        self::assertEquals(1, $newLocation->getState());
        self::assertNotEquals($location->getData()->getId(), $newLocation->getData()?->getId());
        self::assertNull($newLocation->getData());

        self::assertEquals(
            '123',
            self::$orm->from(StubLocationData::class)
                ->where('id', 6)
                ->get()
                ->data
        );
    }

    public function testUpdateSelNull()
    {
        $mapper = $this->createTestMapper(Action::SET_NULL);

        /** @var StubLocation $location */
        $location = $mapper->findOne(2);

        $location->setId(null);
        $location->setState(2);
        $location->getData()->setData('Aragorn');

        $mapper->saveOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(8);

        self::assertEquals(2, $newLocation->getState());
        self::assertNull($newLocation->getData());

        self::assertEquals(
            0,
            self::$orm->from(StubLocationData::class)
                ->where('id', $location->getData()->getId())
                ->get()
                ->location_id
        );
    }

    public function testDelete()
    {
        $mapper = $this->createTestMapper();

        /** @var StubLocation $location */
        $location = $mapper->findOne(3);

        $dataId = $location->getData()->getId();

        $mapper->delete($location);

        self::assertEquals(8, $dataId);
        self::assertNull(
            self::$orm->findOne(StubLocation::class, 3)
        );
        self::assertNull(
            self::$orm->findOne(StubLocationData::class, $dataId)
        );
    }

    public function testDeleteNoAction()
    {
        $mapper = $this->createTestMapper(Action::CASCADE, Action::NO_ACTION);

        /** @var StubLocation $location */
        $location = $mapper->findOne(4);

        $dataId = $location->getData()->getId();

        $mapper->delete($location);

        self::assertEquals(9, $dataId);
        self::assertNull(
            self::$orm->findOne(StubLocation::class, 4)
        );
        self::assertEquals(
            4,
            self::$orm->findOne(StubLocationData::class, $dataId)->getLocationNo()
        );
        self::assertEquals(
            '壘。汝可引本部五百餘人，以天書三卷授之，曰：「此張角正殺敗董卓回寨。玄德謂關、張寶勢窮力乏，必獲惡。',
            self::$orm->findOne(StubLocationData::class, $dataId)->getData()
        );
    }

    public function testDeleteSetNull()
    {
        $mapper = $this->createTestMapper(Action::CASCADE, Action::SET_NULL);

        /** @var StubLocation $location */
        $location = $mapper->findOne(5);

        $dataId = $location->getData()->getId();

        $mapper->delete($location);

        self::assertEquals(10, $dataId);
        self::assertNull(
            self::$orm->findOne(StubLocation::class, 5)
        );
        self::assertEquals(
            0,
            self::$orm->findOne(StubLocationData::class, $dataId)->getLocationNo()
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
            ->target(StubSakura::class, ['id' => 'location'])
            ->flush($flush)
            ->onUpdate($onUpdate)
            ->onDelete($onDelete);

        $rm->oneToMany('roses')
            ->target(StubRose::class, ['id' => 'location'])
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
