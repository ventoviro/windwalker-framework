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
use Windwalker\ORM\Actions\SelectAction;
use Windwalker\ORM\ORM;

/**
 * The SelectActionTest class.
 */
class SelectActionTest extends AbstractDatabaseTestCase
{
    protected SelectAction $instance;

    public function testAutoSelections()
    {
        $this->instance->select('*')
            ->from('ww_flower', 'f')
            ->leftJoin('ww_categories', 'c', 'c.id', 'f.catid')
            ->limit(3)
            ->autoSelections()
            ->groupByDivider();

        $items = $this->instance->all();

        show(json_encode($items, JSON_PRETTY_PRINT));
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
        $this->instance = new SelectAction(new ORM(self::$db));
    }
}
