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

        self::assertEquals(6, $data->getId());
        self::assertEquals(
            '「至難得者，謂操曰：運籌決算有神功，二虎還須遜一龍。初到任，即設五色棒十餘條於縣之四門。有犯禁者，。',
            $data->getData()
        );
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
