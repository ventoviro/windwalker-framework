<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Relation\Strategy;

use Windwalker\Database\Schema\Schema;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\StubAction;
use Windwalker\ORM\Test\Entity\StubMember;
use Windwalker\ORM\Test\Entity\StubMemberActionMap;
use Windwalker\ORM\Test\Entity\StubSakura;
use Windwalker\ORM\Test\Entity\StubSakuraRoseMap;
use Windwalker\Utilities\Classes\DocblockHelper;

/**
 * The ManyMorphMany class.
 */
class ManyMorphManyTest extends AbstractORMTestCase
{
    public function testLoad()
    {
        $memberMapper = $this->createMemberMapper();

        /** @var StubMember $member */
        $member = $memberMapper->findOne(1);

        $actions = $member->getActions();

        self::assertEquals(
            [
                'ACS10001',
                'ACS10002',
                'ACS10003',
                'ACS10004',
                'ACS10005'
            ],
            $actions->loadColumn('no')->unique()->dump()
        );

        self::assertEquals(
            ['member'],
            $actions->loadColumn('type')->unique()->dump()
        );

        $mapTypes = $actions->all()
            ->map(fn (StubAction $action) => $action->getMap()?->getType())
            ->unique()
            ->dump();

        self::assertEquals(
            ['student'],
            $mapTypes
        );
    }

    public function testCreate()
    {
        $memberMapper = $this->createMemberMapper();

        $member = new StubMember();
        $member->setNo('MM0001');
        $member->setName('MM');
        $member->setEmail('mm@test.com');
        $member->setAvatar('mm.jpg');

        $actions = $member->getActions();

        $actions->attach(
            StubAction::newInstance()
                ->setNo('MMA001')
                ->setTitle('Action Create 1')
        );

        $actions->attach(
            StubAction::newInstance()
                ->setNo('MMA002')
                ->setTitle('Action Create 2')
        );

        $memberMapper->createOne($member);

        /** @var StubMember $newMember */
        $newMember = $memberMapper->findOne(['no' => 'MM0001']);

        self::assertEquals(
            1227,
            $newMember->getActions()[0]->getId()
        );

        self::assertEquals(
            'MMA001',
            $newMember->getActions()[0]->getNo()
        );
        self::assertEquals(
            1228,
            $newMember->getActions()[1]->getId()
        );

        /** @var StubMemberActionMap $map */
        $map = $newMember->getActions()[0]->getMap();

        self::assertEquals(
            ['MM0001', 'MMA001', 'student'],
            [
                $map->getMemberNo(),
                $map->getActionNo(),
                $map->getType()
            ]
        );
    }

    public function testUpdateAttachAndDetach()
    {
        $memberMapper = $this->createMemberMapper();

        /** @var StubMember $member */
        $member = $memberMapper->findOne(1);

        $actionCollection = $member->getActions();
        $actionCollection->detach($actionCollection[0]);
        $actionCollection->attach(
            StubAction::newInstance()
                ->setNo('MMA003')
                ->setTitle('Action Create 3')
        );

        $memberMapper->updateOne($member);

        /** @var StubMember $newMember */
        $newMember = $memberMapper->findOne(1);

        self::assertEquals(
            [
                'ACS10002',
                'ACS10003',
                'ACS10004',
                'ACS10005',
                'MMA003'
            ],
            $newMember->getActions()->loadColumn('no')
                ->unique()
                ->values()
                ->dump()
        );
    }

    public function testUpdateSync()
    {
        $memberMapper = $this->createMemberMapper();

        /** @var StubMember $member */
        $member = $memberMapper->findOne(1);

        $actionCollection = $member->getActions();
        $actionCollection->sync(
            [
                StubAction::newInstance()
                    ->setNo('MMA004')
                    ->setTitle('Action Create 4')
            ]
        );

        $memberMapper->updateOne($member);

        /** @var StubMember $newMember */
        $newMember = $memberMapper->findOne(1);

        self::assertEquals(
            [
                'MMA004'
            ],
            $newMember->getActions()->loadColumn('no')
                ->unique()
                ->values()
                ->dump()
        );
    }

    public function testUpdateCascade()
    {
        $memberMapper = $this->createMemberMapper();

        /** @var StubMember $member */
        $member = $memberMapper->findOne(2);

        $member->setNo($member->getNo() . '-2');

        $actionCollection = $member->getActions();
        $actionCollection->detach($actionCollection[0]);
        $actionCollection->attach(
            StubAction::newInstance()
                ->setNo('MMA005')
                ->setTitle('Action Create 5')
        );

        $memberMapper->updateOne($member);

        /** @var StubMember $newMember */
        $newMember = $memberMapper->findOne(2);

        self::assertEquals(
            [
                'U00002-2',
            ],
            $newMember->getActions()->all()
                ->map(fn (StubAction $action) => $action->getMap()->getMemberNo())
                ->unique()
                ->values()
                ->dump()
        );
    }

    public function testUpdateSetNull()
    {
        $memberMapper = $this->createMemberMapper(Action::SET_NULL);

        /** @var StubMember $member */
        $member = $memberMapper->findOne(3);

        $member->setNo($member->getNo() . '-2');

        $aids = $member->getActions()->loadColumn('id')->dump();

        $memberMapper->updateOne($member);

        /** @var StubMember $newMember */
        $newMember = $memberMapper->findOne(3);

        self::assertEquals(
            [],
            $newMember->getActions()->all()
                ->map(fn (StubAction $action) => $action->getMap()->getMemberNo())
                ->unique()
                ->values()
                ->dump()
        );

        self::assertEquals(
            $aids,
            self::$orm->mapper(StubAction::class)
                ->select()
                ->where('id', $aids)
                ->loadColumn('id')
                ->dump()
        );
    }

    public function testDeleteCascade()
    {
        $memberMapper = $this->createMemberMapper();

        $actions1 = self::$orm->select()
            ->from(StubAction::class)
            ->leftJoin(StubMemberActionMap::class)
            ->where('member_action_map.member_no', 'U00005')
            ->all();

        $nos = $actions1->column('no')->unique()->values()->dump();

        /** @var StubMember $member */
        $memberMapper->delete(['no' =>'U00005']);

        $actions2 = self::$orm->select()
            ->from(StubAction::class)
            ->leftJoin(StubMemberActionMap::class)
            ->where('member_action_map.member_no', 'U00005')
            ->autoSelections()
            ->all();

        self::assertCount(
            0,
            $actions2
        );

        $actions3 = self::$orm->select()
            ->from(StubAction::class)
            ->where('action.no', $nos)
            ->all();

        self::assertCount(
            10,
            $actions3
        );

        self::assertEquals(
            ['system'],
            $actions3->column('type')->unique()->dump()
        );
    }

    public function testDeleteSetNull()
    {
        $memberMapper = $this->createMemberMapper(Action::CASCADE, Action::SET_NULL);

        $actions1 = self::$orm->select()
            ->from(StubAction::class)
            ->leftJoin(StubMemberActionMap::class)
            ->where('member_action_map.member_no', 'U00006')
            ->all();

        $nos = $actions1->column('no')->unique()->values()->dump();

        /** @var StubMember $member */
        $memberMapper->delete(['no' =>'U00006']);

        $actions2 = self::$orm->select()
            ->from(StubAction::class)
            ->leftJoin(StubMemberActionMap::class)
            ->where('member_action_map.member_no', 'U00006')
            ->autoSelections()
            ->all();

        self::assertCount(
            0,
            $actions2
        );

        $actions3 = self::$orm->select()
            ->from(StubAction::class)
            ->where('action.no', $nos)
            ->all();

        self::assertCount(
            20,
            $actions3
        );

        self::assertEquals(
            ['member', 'system'],
            $actions3->column('type')->unique()->values()->dump()
        );
    }

    public function createMemberMapper(
        string $onUpdate = Action::CASCADE,
        string $onDelete = Action::CASCADE,
        bool $flush = false
    ) {
        $mapper = self::$orm->mapper(StubMember::class);

        $mapper->getMetadata()
            ->getRelationManager()
            ->getRelation('actions')
            ->onUpdate($onUpdate)
            ->onDelete($onDelete);

        return $mapper;
    }

    public function createActionMapper(
        string $onUpdate = Action::CASCADE,
        string $onDelete = Action::CASCADE,
        bool $flush = false
    ) {
        $mapper = self::$orm->mapper(StubAction::class);

        $mapper->getMetadata()
            ->getRelationManager()
            ->getRelation('members')
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
