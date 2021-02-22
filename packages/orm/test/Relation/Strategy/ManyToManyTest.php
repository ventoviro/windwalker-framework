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
        $sakuraMapper = $this->createSakuraMapper();

        /** @var StubSakura $sakura */
        $sakura = $sakuraMapper->findOne(1);
        $roses = $sakura->getRoses();

        self::assertEquals(
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
            '2020-11-01 00:37:05',
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
        show($newSakura->getRoses()->all());
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
