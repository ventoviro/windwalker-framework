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

        $pageAttachments = $item->getPageAttachments()->all();
        $articleAttachments = $item->getArticleAttachments()->all();

        self::assertEquals([56, 57, 58, 59, 60], $pageAttachments->column('id', null, true)->dump());
        self::assertEquals([61, 62, 63, 64, 65], $articleAttachments->column('id', null, true)->dump());
    }

    public function testCreate()
    {
        $mapper = $this->createTestMapper();

        $page = new StubPage();
        $page->setNo('P00011');
        $page->setTitle('Create Page 1');

        $attachment1 = new StubAttachment();
        $attachment1->setFile('hello1.zip');
        $attachment1->setNo('AP21001');

        $attachment2 = new StubAttachment();
        $attachment2->setFile('hello2.zip');
        $attachment2->setNo('AA22002');

        $page->getPageAttachments()->attach($attachment1);
        $page->getArticleAttachments()->attach($attachment2);

        $mapper->createOne($page);

        /** @var StubPage $newPage */
        $newPage = $mapper->findOne(['no' => 'P00011']);

        $pa = $newPage->getPageAttachments()->all();
        $aa = $newPage->getArticleAttachments()->all();

        self::assertEquals([136], $pa->column('id', null, true)->dump());
        self::assertEquals(['AP21001'], $pa->column('no', null, true)->dump());
        self::assertEquals([137], $aa->column('id', null, true)->dump());
        self::assertEquals(['AA22002'], $aa->column('no', null, true)->dump());
    }

    public function testUpdate()
    {
        $mapper   = $this->createTestMapper();
        /** @var StubPage $page */
        $page = $mapper->findOne(1);

        $attachments = $page->getPageAttachments();
        $attachments->detach($attachments[0]);

        $attachments->attach(
            StubAttachment::newInstance()
                ->setNo('AP12001')
                ->setFile('AP12001.zip')
        );

        $mapper->updateOne($page);

        /** @var StubPage $newPage */
        $newPage = $mapper->findOne(1);

        $atts = $newPage->getPageAttachments()->all();

        self::assertEquals(
            [37, 38, 39, 40, 138],
            $atts->column('id', null, true)->dump()
        );

        self::assertEquals(
            [$page->getNo()],
            $atts->column('targetNo', null, true)->unique()->dump()
        );
    }

    public function testUpdateSelNull()
    {
        $mapper = $this->createTestMapper(Action::SET_NULL);

        /** @var StubPage $page */
        $page = $mapper->findOne(2);

        $page->setNo($page->getNo() . '-2');
        $attIds = $page->getPageAttachments()->all()->column('id', null, true)->dump();

        $mapper->saveOne($page);

        /** @var StubPage $newPage */
        $newPage = $mapper->findOne(2);

        self::assertCount(0, $newPage->getPageAttachments()->all());

        $atts = self::$orm->from(StubAttachment::class)
            ->where('id', 'in', $attIds)
            ->all();

        self::assertCount(
            5,
            $atts
        );

        self::assertEquals(
            [null],
            $atts->column('target_no')->unique()->dump()
        );
    }

    public function testDelete()
    {
        $att = new StubAttachment();
        $att->setNo('AC00001');
        $att->setFile('Hello.zip');
        $att->setType('category');
        $att->setTargetNo('P00003'); // Same with current page

        $att = self::$orm->mapper($att::class)->createOne($att);

        $mapper = $this->createTestMapper();

        /** @var StubPage $page */
        $page = $mapper->findOne(3);

        $mapper->delete($page);

        $att2 = self::$db->select()
            ->from($att::class)
            ->where('id', 139)
            ->get();

        self::assertNotNull($att2);

        self::assertCount(
            0,
            self::$db->select()
                ->from($att::class)
                ->where('type', 'article')
                ->where('target_no', $page->getNo())
                ->all()
        );
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
            ->morphBy(['type' => 'page'])
            ->flush($flush)
            ->onUpdate($onUpdate)
            ->onDelete($onDelete);

        $mapper->getMetadata()
            ->getRelationManager()
            ->oneToMany('articleAttachments')
            ->target(StubAttachment::class, 'no', 'target_no')
            ->morphBy(['type' => 'article'])
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
