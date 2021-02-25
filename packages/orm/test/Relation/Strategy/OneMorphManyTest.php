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
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\StubAttachment;
use Windwalker\ORM\Test\Entity\StubLocation;
use Windwalker\ORM\Test\Entity\StubMember;
use Windwalker\ORM\Test\Entity\StubPage;

/**
 * The OneMorphManyTest class.
 */
class OneMorphManyTest extends AbstractORMTestCase
{
    public function testLoad()
    {
        $mapper = $this->createTestMapper();

        /** @var StubPage $item */
        $item = $mapper->findOne(3);

        $pageAttachments = $item->getPageAttachments();
        $articleAttachments = $item->getArticleAttachments();

        show(
            $pageAttachments->all(),
            $articleAttachments->all()
        );

        // self::assertEquals([11, 12, 13, 14, 15], $sakuras->all(Collection::class)->column('id')->dump());
        // self::assertEquals([11, 12, 13, 14, 15], $roses->all(Collection::class)->column('id')->dump());
    }

    public function createTestMapper(
        string $onUpdate = Action::CASCADE,
        string $onDelete = Action::CASCADE,
        bool $flush = false
    ) {
        $mapper = self::$orm->mapper(StubPage::class);

        $mapper->getMetadata()
            ->getRelationManager()
            ->oneToMany('pageAttachments')
            ->target(StubAttachment::class, 'no', 'target_no')
            ->morphBy(type: 'page')
            ->flush($flush)
            ->onUpdate($onUpdate)
            ->onDelete($onDelete);

        $mapper->getMetadata()
            ->getRelationManager()
            ->oneToMany('articleAttachments')
            ->target(StubAttachment::class, 'no', 'target_no')
            ->morphBy(type: 'article')
            ->flush($flush)
            ->onUpdate($onUpdate)
            ->onDelete($onDelete);

        return $mapper;
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/../../Stub/morph-relations.sql');
    }
}
