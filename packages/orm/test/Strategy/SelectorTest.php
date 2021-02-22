<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Strategy;

use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\ORM\Strategy\Selector;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\Article;
use Windwalker\ORM\Test\Entity\Category;
use Windwalker\ORM\Test\Entity\Flower;
use Windwalker\ORM\Test\Entity\StubRose;
use Windwalker\ORM\Test\Entity\StubSakura;
use Windwalker\ORM\Test\Entity\StubSakuraRoseMap;

/**
 * The SelectActionTest class.
 */
class SelectorTest extends AbstractORMTestCase
{
    protected Selector $instance;

    public function testGroupByJoins()
    {
        $this->instance->select('*')
            ->from(Flower::class, 'f')
            ->leftJoin(Category::class, 'c', 'c.id', 'f.catid')
            ->limit(3)
            ->groupByJoins();

        $items = $this->instance->all();

        self::assertEquals(
            $items->dump(true),
            [
                [
                    'id' => '1',
                    'catid' => '2',
                    'title' => 'Alstroemeria',
                    'meaning' => 'aspiring',
                    'ordering' => '1',
                    'state' => '0',
                    'params' => '',
                    'c' => [
                        'id' => '2',
                        'title' => 'Bar',
                        'ordering' => '2',
                        'params' => ''
                    ]
                ],
                [
                    'id' => '2',
                    'catid' => '2',
                    'title' => 'Amaryllis',
                    'meaning' => 'dramatic',
                    'ordering' => '2',
                    'state' => '0',
                    'params' => '',
                    'c' => [
                        'id' => '2',
                        'title' => 'Bar',
                        'ordering' => '2',
                        'params' => ''
                    ]
                ],
                [
                    'id' => '3',
                    'catid' => '1',
                    'title' => 'Anemone',
                    'meaning' => 'fragile',
                    'ordering' => '3',
                    'state' => '0',
                    'params' => '',
                    'c' => [
                        'id' => '1',
                        'title' => 'Foo',
                        'ordering' => '1',
                        'params' => ''
                    ]
                ]
            ]
        );
    }

    public function testAutoJoin()
    {
        self::importFromFile(__DIR__ . '/../Stub/relations.sql');

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
            );

        $this->instance->from(StubSakura::class)
            ->leftJoin(StubSakuraRoseMap::class)
            ->leftJoin(StubRose::class)
            ->where('sakura.no', 'S00001')
            ->groupByJoins();

        self::assertSqlFormatEquals(
            <<<SQL
            SELECT `sakura`.`id`                 AS `id`,
                   `sakura`.`no`                 AS `no`,
                   `sakura`.`location_no`        AS `location_no`,
                   `sakura`.`rose_no`            AS `rose_no`,
                   `sakura`.`title`              AS `title`,
                   `sakura`.`state`              AS `state`,
                   `sakura_rose_map`.`sakura_no` AS `sakura_rose_map.sakura_no`,
                   `sakura_rose_map`.`rose_no`   AS `sakura_rose_map.rose_no`,
                   `sakura_rose_map`.`type`      AS `sakura_rose_map.type`,
                   `sakura_rose_map`.`created`   AS `sakura_rose_map.created`,
                   `rose`.`id`                   AS `rose.id`,
                   `rose`.`no`                   AS `rose.no`,
                   `rose`.`location_no`          AS `rose.location_no`,
                   `rose`.`sakura_no`            AS `rose.sakura_no`,
                   `rose`.`title`                AS `rose.title`,
                   `rose`.`state`                AS `rose.state`
            FROM `sakuras` AS `sakura`
                     LEFT JOIN `sakura_rose_maps` AS `sakura_rose_map` ON `sakura`.`no` = `sakura_rose_map`.`sakura_no`
                     LEFT JOIN `roses` AS `rose` ON `sakura_rose_map`.`rose_no` = `rose`.`no`
            WHERE `sakura`.`no` = 'S00001'
            SQL,
            $this->instance->debug(false, false, true)
        );
    }

    public function testGroupWithEntity()
    {
        $this->instance->select('*')
            ->from(Article::class, 'a')
            ->leftJoin(Category::class, 'c', 'c.id', 'a.category_id')
            ->limit(1)
            ->groupByJoins();

        $item = $this->instance->all(Article::class)->first();

        self::assertInstanceOf(Article::class, $item);

        self::assertEquals(
            [
                'id' => 2,
                'title' => 'Bar',
                'ordering' => 2,
                'params' => '""'
            ],
            $this->instance->getORM()->extractEntity($item->c)
        );
    }

    public function testSelectOne()
    {
        /** @var Article $item */
        $item = $this->instance->from(Article::class)
            ->order('id', 'DESC')
            ->get(Article::class);

        self::assertInstanceOf(
            Article::class,
            $item
        );

        self::assertEquals(
            'Vel nisi est.',
            $item->getTitle()
        );

        self::assertEquals(
            15,
            $item->getId()
        );
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/../Stub/data.sql');
    }

    protected function setUp(): void
    {
        $this->instance = new Selector(self::$orm);
    }
}
