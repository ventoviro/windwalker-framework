<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Relation\Strategy;

use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\Location;
use Windwalker\ORM\Test\Entity\LocationData;
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

        /** @var Location $item */
        $item = $mapper->findOne(3);

        $sakuras = $item->getSakuras();
        $roses = $item->getRoses();

        self::assertEquals([11, 12, 13, 14, 15], $sakuras->all()->column('id')->dump());
        self::assertEquals([11, 12, 13, 14, 15], $roses->all()->column('id')->dump());
    }

    public function testLoadAll()
    {
        $mapper = $this->createTestMapper();

        /** @var Location $item */
        $item = $mapper->findOne(1);

        $encoded = json_encode($item);

        self::assertEquals(
            '「至難得者，謂操曰：運籌決算有神功，二虎還須遜一龍。初到任，即設五色棒十餘條於縣之四門。有犯禁者，。',
            json_decode($encoded, true)['data']['data'],
        );
    }

    public function testCreate()
    {
        $mapper = $this->createTestMapper();

        $location = new Location();
        $location->setTitle('Location Create 1');

        $data = new LocationData();
        $data->setData('Location Data Create 1');

        $location->setData($data);

        $mapper->createOne($location);

        /** @var Location $newLocation */
        $newLocation = $mapper->findOne(['title' => 'Location Create 1']);

        $data = $newLocation->getData();

        self::assertEquals(11, $data->getId());
        self::assertEquals('Location Data Create 1', $data->getData());
    }

    public function testUpdate()
    {
        $mapper   = $this->createTestMapper();
        /** @var Location $location */
        $location = $mapper->findOne(1);

        $location->setState(2);
        $location->getData()->setData('123');

        $mapper->updateOne($location);

        /** @var Location $newLocation */
        $newLocation = $mapper->findOne(1);

        self::assertEquals(2, $newLocation->getState());
        self::assertEquals($location->getState(), $newLocation->getState());
        self::assertEquals(
            $newLocation->getData()->getData(),
            self::$orm->from(LocationData::class)
                ->where('id', 6)
                ->get()
                ->data
        );

        // Update Without child value
        /** @var Location $location */
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

        /** @var Location $location */
        $location = $mapper->findOne(1);

        $location->setId(null);
        $location->setState(1);
        $location->getData()->setData('Gandalf');

        $mapper->saveOne($location);

        /** @var Location $newLocation */
        $newLocation = $mapper->findOne(7);

        self::assertEquals(1, $newLocation->getState());
        self::assertNotEquals($location->getData()->getId(), $newLocation->getData()?->getId());
        self::assertNull($newLocation->getData());

        self::assertEquals(
            '123',
            self::$orm->from(LocationData::class)
                ->where('id', 6)
                ->get()
                ->data
        );
    }

    public function testUpdateSelNull()
    {
        $mapper = $this->createTestMapper(Action::SET_NULL);

        /** @var Location $location */
        $location = $mapper->findOne(2);

        $location->setId(null);
        $location->setState(2);
        $location->getData()->setData('Aragorn');

        $mapper->saveOne($location);

        /** @var Location $newLocation */
        $newLocation = $mapper->findOne(8);

        self::assertEquals(2, $newLocation->getState());
        self::assertNull($newLocation->getData());

        self::assertEquals(
            0,
            self::$orm->from(LocationData::class)
                ->where('id', $location->getData()->getId())
                ->get()
                ->location_id
        );
    }

    public function testDelete()
    {
        $mapper = $this->createTestMapper();

        /** @var Location $location */
        $location = $mapper->findOne(3);

        $dataId = $location->getData()->getId();

        $mapper->delete($location);

        self::assertEquals(8, $dataId);
        self::assertNull(
            self::$orm->findOne(Location::class, 3)
        );
        self::assertNull(
            self::$orm->findOne(LocationData::class, $dataId)
        );
    }

    public function testDeleteNoAction()
    {
        $mapper = $this->createTestMapper(Action::CASCADE, Action::NO_ACTION);

        /** @var Location $location */
        $location = $mapper->findOne(4);

        $dataId = $location->getData()->getId();

        $mapper->delete($location);

        self::assertEquals(9, $dataId);
        self::assertNull(
            self::$orm->findOne(Location::class, 4)
        );
        self::assertEquals(
            4,
            self::$orm->findOne(LocationData::class, $dataId)->getLocationId()
        );
        self::assertEquals(
            '壘。汝可引本部五百餘人，以天書三卷授之，曰：「此張角正殺敗董卓回寨。玄德謂關、張寶勢窮力乏，必獲惡。',
            self::$orm->findOne(LocationData::class, $dataId)->getData()
        );
    }

    public function testDeleteSetNull()
    {
        $mapper = $this->createTestMapper(Action::CASCADE, Action::SET_NULL);

        /** @var Location $location */
        $location = $mapper->findOne(5);

        $dataId = $location->getData()->getId();

        $mapper->delete($location);

        self::assertEquals(10, $dataId);
        self::assertNull(
            self::$orm->findOne(Location::class, 5)
        );
        self::assertEquals(
            0,
            self::$orm->findOne(LocationData::class, $dataId)->getLocationId()
        );
    }

    public function createTestMapper(
        string $onUpdate = Action::CASCADE,
        string $onDelete = Action::CASCADE,
        bool $flush = false
    ) {
        $mapper = self::$orm->mapper(Location::class);
        $rm = $mapper->getMetadata()
            ->getRelationManager();

        $rm->oneToMany('sakuras')
            ->target(StubSakura::class, ['id' => 'location_id'])
            ->flush($flush)
            ->onUpdate($onUpdate)
            ->onDelete($onDelete);

        $rm->oneToMany('roses')
            ->target(StubRose::class, ['id' => 'location_id'])
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
