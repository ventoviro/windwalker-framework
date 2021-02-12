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
use Windwalker\ORM\Test\Entity\Article;
use Windwalker\ORM\Test\Entity\Category;
use Windwalker\ORM\Test\Entity\Flower;

/**
 * The SelectActionTest class.
 */
class SelectorTest extends AbstractDatabaseTestCase
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

    public function testGroupWithEtity()
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
                'params' => ''
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
        $this->instance = new Selector(new ORM(self::$db));
    }
}
