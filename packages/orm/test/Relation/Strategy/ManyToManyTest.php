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
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Relation\RelationCollection;
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\StubLocation;
use Windwalker\ORM\Test\Entity\StubRose;
use Windwalker\ORM\Test\Entity\StubSakura;
use Windwalker\ORM\Test\Entity\StubSakuraRoseMap;

/**
 * The ManyToManyTest class.
 */
class ManyToManyTest extends AbstractORMTestCase
{
    public function testLoad()
    {
        // Prepare Entity relations
        $this->createRoseMapper();

        $sakuraMapper = $this->createSakuraMapper();

        /** @var StubSakura $sakura */
        $sakura = $sakuraMapper->findOne(1);
        $roses = $sakura->getRoses();

        self::assertArraySimilar(
            [
                'S00015',
                'S00013',
                'S00014',
                'S00008',
                'S00018',
                'S00025'
            ],
            $roses->all()->column('sakuraNo', null, true)->dump()
        );
    }

    public function testLoadWithMap()
    {
        $sakuraMapper = $this->createSakuraMapper();

        /** @var StubSakura $sakura */
        $sakura = $sakuraMapper->findOne(1);
        /** @var StubRose[]|RelationCollection $roses */
        $roses = $sakura->getRoses();

        $date = $roses[0]->getMap()->getCreated()->format(self::$db->getDateFormat());

        self::assertEquals(
            '2020-12-06 06:11:36',
            $date
        );
    }

    public function testCreate()
    {
        $sakuraMapper = $this->createSakuraMapper();

        $sakura = new StubSakura();
        $sakura->setTitle('New Sakura 1');
        $sakura->setNo('S10001');
        $sakura->setState(1);

        $roses = $sakura->getRoses();

        $roses->attach(
            StubRose::newInstance()
                ->setTitle('New Rose 1')
                ->setNo('R10001')
        );

        $roses->attach(
            $this->createRoseMapper()->findOne(2)
        );

        $sakuraMapper->createOne($sakura);

        /** @var StubSakura $newSakura */
        $newSakura = $sakuraMapper->findOne(['no' => 'S10001']);

        self::assertArraySimilar(
            ['26', '2'],
            $newSakura->getRoses()
                ->all(Collection::class)
                ->column('id', null, true)
                ->dump()
        );
    }

    public function testUpdateAttachAndDetach()
    {
        $sakuraMapper = $this->createSakuraMapper();

        /** @var StubSakura $sakura */
        $sakura = $sakuraMapper->findOne(1);
        $sakura->setState(2);
        $roses = $sakura->getRoses();

        $roses->attach(
            StubRose::newInstance()
                ->setTitle('New Rose 2')
                ->setNo('R10002')
        );

        $roses->detach($roses[0]);

        $sakuraMapper->updateOne($sakura);

        /** @var StubSakura $newSakura */
        $newSakura = $sakuraMapper->findOne(1);

        self::assertEquals(
            2,
            $newSakura->getState()
        );

        self::assertArraySimilar(
            [
                'R00017',
                'R00015',
                'R00004',
                'R00009',
                'R00011',
                'R10002'
            ],
            $newSakura->getRoses()
                ->all()
                ->column('no', null, true)
                ->dump()
        );

        self::assertArraySimilar(
            [
                17,
                15,
                4,
                9,
                11,
                27
            ],
            $newSakura->getRoses()
                ->all()
                ->column('id', null, true)
                ->map('intval')
                ->dump()
        );
    }

    public function testUpdateSync()
    {
        $roseMapper = $this->createRoseMapper();

        /** @var StubRose $rose */
        $rose = $roseMapper->findOne(1);
        $rose->setState(2);
        $sakurasCollection = $rose->getSakuras();
        $sakuras = $sakurasCollection->all(StubSakura::class);

        $sakuras[0] = (new StubSakura())
            ->setTitle('Create Sakura 2')
            ->setNo('S10002')
            ->setState(1);

        $sakurasCollection->sync($sakuras);

        $roseMapper->updateOne($rose);

        /** @var StubRose $newRose */
        $newRose = $roseMapper->findOne(1);

        $newSakuras = $newRose->getSakuras()->all(Collection::class);

        self::assertEquals(
            [7, 10, 14, 20, 27],
            $newSakuras->column('id')->dump()
        );

        self::assertEquals(
            [
                'S00007',
                'S00010',
                'S00014',
                'S00020',
                'S10002'
            ],
            $newSakuras->column('no', null, true)
                ->dump()
        );
    }

    public function testUpdateCascade()
    {
        $sakuraMapper = $this->createSakuraMapper();

        /** @var StubSakura $sakura */
        $sakura = $sakuraMapper->findOne(1);

        $sakura->setNo('S00001-2');

        $roses = $sakura->getRoses();
        $ids = $roses->all(Collection::class)->column('id')->dump();

        $sakuraMapper->updateOne($sakura);

        /** @var StubSakura $newSakura */
        $newSakura = $sakuraMapper->findOne(1);

        $roses = $newSakura->getRoses();
        $ids2 = $roses->all(Collection::class)->column('id')->dump();

        self::assertEquals(
            $ids,
            $ids2
        );
    }

    public function testUpdateSetNull()
    {
        $sakuraMapper = $this->createSakuraMapper(Action::SET_NULL);

        /** @var StubSakura $sakura */
        $sakura = $sakuraMapper->findOne(1);

        $sakura->setNo('S00001-3');

        $sakuraMapper->updateOne($sakura);

        /** @var StubSakura $newSakura */
        $newSakura = $sakuraMapper->findOne(1);

        $roses = $newSakura->getRoses();
        $ids = $roses->all(Collection::class)->column('id')->dump();

        self::assertEquals(
            [],
            $ids
        );

        $nullMaps = self::$orm->mapper(StubSakuraRoseMap::class)
            ->select()
            ->where('sakura_no', '')
            ->all();

        self::assertCount(
            0,
            $nullMaps,
            'Map with empty sakura_no should be 0'
        );

        $nullMaps = self::$orm->mapper(StubSakuraRoseMap::class)
            ->select()
            ->where('sakura_no', 'S00001-2')
            ->all();

        self::assertCount(
            0,
            $nullMaps,
            'Map with sakura_no: S00001-2 should be 0'
        );
    }

    public function testUpdateSyncCascade()
    {
        $roseMapper = $this->createRoseMapper();

        /** @var StubRose $rose */
        $rose = $roseMapper->findOne(2);
        $rose->setState(2);
        $rose->setNo('R00002-2');
        $sakurasCollection = $rose->getSakuras();
        $sakuras = $sakurasCollection->all(StubSakura::class);

        $sakuras[0] = (new StubSakura())
            ->setTitle('Create Sakura 3')
            ->setNo('S10003')
            ->setState(1);

        $sakurasCollection->sync($sakuras);

        $roseMapper->updateOne($rose);

        /** @var StubRose $newRose */
        $newRose = $roseMapper->findOne(2);

        $newSakuras = $newRose->getSakuras()->all(Collection::class);

        self::assertArraySimilar(
            [
                '10',
                '14',
                '16',
                '20',
                '23',
                '26',
                '28'
            ],
            $newSakuras->column('id')->dump()
        );

        self::assertEquals(
            [
                'S00010',
                'S00014',
                'S00016',
                'S00020',
                'S00023',
                'S10001',
                'S10003'
            ],
            $newSakuras->column('no', null, true)
                ->dump()
        );
    }

    public function testDeleteSetNull()
    {
        $sakuraMapper = $this->createSakuraMapper(Action::CASCADE, Action::SET_NULL);

        /** @var StubSakura $sakura */
        $sakura = $sakuraMapper->findOne(['no' => 'S00004']);
        $roses = $sakura->getRoses();

        $nos = $roses->all(Collection::class)->column('no')->dump();

        $sakuraMapper->delete($sakura);

        self::assertCount(
            0,
            $roses->clearCache()->all()
        );

        $roses = self::$orm->mapper(StubRose::class)
            ->select()
            ->where(['no' => $nos])
            ->all();

        self::assertCount(
            3,
            $roses
        );
    }

    public function testDeleteCascade()
    {
        $sakuraMapper = $this->createSakuraMapper();

        /** @var StubSakura $sakura */
        $sakura = $sakuraMapper->findOne(['no' => 'S00003']);
        $roses = $sakura->getRoses();

        $nos = $roses->all(Collection::class)->column('no')->dump();

        $sakuraMapper->delete($sakura);

        self::assertCount(
            0,
            $roses->clearCache()->all()
        );

        $roses = self::$orm->mapper(StubRose::class)
            ->select()
            ->where(['no' => $nos])
            ->all();

        self::assertCount(
            0,
            $roses
        );
    }

    public function createRoseMapper(
        string $onUpdate = Action::CASCADE,
        string $onDelete = Action::CASCADE,
        bool $flush = false
    ) {
        $mapper = self::$orm->mapper(StubRose::class);

        $mapper->getMetadata()
            ->getRelationManager()
            ->manyToMany('sakuras')
            ->mapBy(
                StubSakuraRoseMap::class,
                'no',
                'rose_no',
            )
            ->target(
                StubSakura::class,
                'sakura_no',
                'no'
            )
            ->onUpdate($onUpdate)
            ->onDelete($onDelete);

        return $mapper;
    }

    public function createSakuraMapper(
        string $onUpdate = Action::CASCADE,
        string $onDelete = Action::CASCADE,
        bool $flush = false
    ) {
        $mapper = self::$orm->mapper(StubSakura::class);

        $mapper->getMetadata()
            ->getRelationManager()
            ->manyToMany('roses')
            ->mapBy(
                StubSakuraRoseMap::class,
                'no',
                'sakura_no',
            )
            ->target(
                StubRose::class,
                'rose_no',
                'no'
            )
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
