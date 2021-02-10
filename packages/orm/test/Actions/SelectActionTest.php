<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Actions;

use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\ORM\Strategy\Selector;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Test\Entity\Category;
use Windwalker\ORM\Test\Entity\Flower;

/**
 * The SelectActionTest class.
 */
class SelectActionTest extends AbstractDatabaseTestCase
{
    protected Selector $instance;

    public function testAutoSelections()
    {
        $this->instance->select('*')
            ->from(Flower::class, 'f')
            ->leftJoin(Category::class, 'c', 'c.id', 'f.catid')
            ->limit(3)
            ->autoSelections()
            ->groupByDivider();

        $tables = $this->instance->getAllTables();

        // show($tables);

        $items = $this->instance->all();

        // show(json_encode($items, JSON_PRETTY_PRINT));
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
        $this->instance = new Selector(self::$db, self::$db->getPlatform()->getGrammar());
    }
}
