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
use Windwalker\ORM\Test\Entity\StubLocation;
use Windwalker\ORM\Test\Entity\StubLocationData;

/**
 * The OneToOneTest class.
 */
class OneToOneTest extends AbstractORMTestCase
{
    public function testLoad()
    {
        $mapper = $this->createTestMapper();

        /** @var StubLocation $item */
        $item = $mapper->findOne(1);

        $data = $item->getData();

        self::assertEquals(6, $data->getId());
        self::assertEquals(
            '「至難得者，謂操曰：運籌決算有神功，二虎還須遜一龍。初到任，即設五色棒十餘條於縣之四門。有犯禁者，。',
            $data->getData()
        );
    }

    public function testAutoLoadRelations()
    {
        $mapper = $this->createTestMapper();

        /** @var StubLocation $item */
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

        $location = new StubLocation();
        $location->setTitle('Location Create 1');

        $data = new StubLocationData();
        $data->setData('Location Data Create 1');

        $location->setData($data);

        $mapper->createOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(['title' => 'Location Create 1']);

        $data = $newLocation->getData();

        self::assertEquals(11, $data->getId());
        self::assertEquals('Location Data Create 1', $data->getData());
    }

    public function testUpdate()
    {
        $mapper = $this->createTestMapper();
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
        $mapper = $this->createTestMapper(Action::NO_ACTION);

        /** @var StubLocation $location */
        $location = $mapper->findOne(1);

        $location->setNo($location->getNo() . '-2');
        $location->setState(1);
        $location->getData()->setData('Gandalf');

        $mapper->saveOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(1);

        self::assertEquals(1, $newLocation->getState());
        self::assertNull(
            $newLocation->getData()
        );
        self::assertNull($newLocation->getData());

        self::assertEquals(
            'L00001',
            self::$orm->from(StubLocationData::class)
                ->where('id', 6)
                ->get()
                ->location_no
        );
    }

    public function testUpdateSelNull()
    {
        $mapper = $this->createTestMapper(Action::SET_NULL);

        /** @var StubLocation $location */
        $location = $mapper->findOne(2);

        $location->setNo($location->getNo() . '-2');
        $location->setState(2);
        $location->getData()->setData('Aragorn');

        $mapper->saveOne($location);

        /** @var StubLocation $newLocation */
        $newLocation = $mapper->findOne(2);

        self::assertEquals(2, $newLocation->getState());
        self::assertNull($newLocation->getData());

        self::assertEquals(
            '',
            self::$orm->from(StubLocationData::class)
                ->where('id', 7)
                ->get()
                ->location_no
        );
    }

    public function testDelete()
    {
        $mapper = $this->createTestMapper();

        /** @var StubLocation $location */
        $location = $mapper->findOne(3);

        $dataId = $location->getData()->getId();

        $mapper->deleteWhere($location);

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

        $mapper->deleteWhere($location);

        self::assertEquals(9, $dataId);
        self::assertNull(
            self::$orm->findOne(StubLocation::class, 4)
        );
        self::assertEquals(
            'L00004',
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

        $mapper->deleteWhere($location);

        self::assertEquals(10, $dataId);
        self::assertNull(
            self::$orm->findOne(StubLocation::class, 5)
        );
        self::assertEquals(
            '',
            self::$orm->findOne(StubLocationData::class, $dataId)->getLocationNo()
        );
    }

    public function createTestMapper(
        string $onUpdate = Action::CASCADE,
        string $onDelete = Action::CASCADE,
        bool $flush = false
    ) {
        $mapper = self::$orm->mapper(StubLocation::class);
        $mapper->getMetadata()
            ->getRelationManager()
            ->getRelation('data')
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
