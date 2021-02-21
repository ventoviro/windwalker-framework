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

        $roses->getQuery()->debug();

        show($roses->all());
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
            ->through(
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
            ->through(
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
