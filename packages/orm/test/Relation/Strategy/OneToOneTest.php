<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Relation\Strategy;

use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\Location;

/**
 * The OneToOneTest class.
 */
class OneToOneTest extends AbstractORMTestCase
{
    public function testLoad()
    {
        $mapper = $this->createTestMapper();

        /** @var Location $item */
        $item = $mapper->findOne(1);

        $data = $item->getData();

        show($data);
    }

    public function createTestMapper()
    {
        return self::$orm->mapper(Location::class);
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/../../Stub/relations.sql');
    }
}
