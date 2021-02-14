<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Test\Entity\Article;

/**
 * The EntityEventTest class.
 */
class EntityEventTest extends AbstractORMTestCase
{
    protected EntityMapper $instance;

    public function testSaveEvent()
    {
        Article::$counter = 0;

        $article = new Article();
        $article->setTitle('Hello');
        $article->setCategoryId(1);
        $article->setContent('World');
        $article->setCreated(new \DateTimeImmutable('now'));
        $article->setState(1);

        /** @var Article $article */
        $article = self::$orm->mapper(Article::class)->createOne($article);

        self::assertEquals(
            1,
            Article::$counter,
        );
        self::assertEquals(2, $article->getCategoryId());

        Article::$counter = 0;
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/Stub/data.sql');
    }
}
