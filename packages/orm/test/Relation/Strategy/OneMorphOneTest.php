<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Relation\Strategy;

use DateTimeImmutable;
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\StubLicense;
use Windwalker\ORM\Test\Entity\StubMember;

/**
 * The OneMorphOneTest class.
 */
class OneMorphOneTest extends AbstractORMTestCase
{
    public function testLoad()
    {
        $mapper = $this->createTestMapper();

        /** @var StubMember $member */
        $member = $mapper->findOne(1);
        $studentLicense = $member->getStudentLicense();

        self::assertEquals(
            'student',
            $studentLicense->getType(),
        );

        self::assertEquals(
            'LS00001',
            $studentLicense->getNo(),
        );
    }

    public function testCreate()
    {
        $mapper = $this->createTestMapper();

        $member = new StubMember();
        $member->setNo('U00008');
        $member->setEmail('test@test.com');

        $lic = new StubLicense();
        $lic->setNo('LS00008');
        $lic->setCreated(new DateTimeImmutable('now'));

        $member->setStudentLicense($lic);

        $mapper->createOne($member);

        /** @var StubMember $newMember */
        $newMember = $mapper->findOne(['no' => 'U00008']);

        $lic = $newMember->getStudentLicense();

        self::assertEquals(15, $lic->getId());
        self::assertEquals('LS00008', $lic->getNo());
    }

    public function testUpdate()
    {
        $mapper = $this->createTestMapper();
        /** @var StubMember $member */
        $member = $mapper->findOne(1);

        $member->setEmail('foo@foo.com');
        $member->getTeacherLicense()->setTitle('License Update 1');

        $mapper->updateOne($member);

        /** @var StubMember $newMember */
        $newMember = $mapper->findOne(1);

        self::assertEquals('foo@foo.com', $newMember->getEmail());
        self::assertEquals($member->getEmail(), $newMember->getEmail());
        self::assertEquals(
            $newMember->getTeacherLicense()->getTitle(),
            self::$orm->from(StubLicense::class)
                ->where('id', 1)
                ->get()
                ->title
        );

        // Update Without child value
        /** @var StubMember $member */
        $member = $mapper->findOne(1);

        $mapper->updateOne($member);

        self::assertEquals(
            'License Update 1',
            $member->getTeacherLicense()->getTitle()
        );
    }

    public function testUpdateSelNull()
    {
        $mapper = $this->createTestMapper(Action::SET_NULL);

        /** @var StubMember $member */
        $member = $mapper->findOne(2);

        $member->setNo($member->getNo() . '-2');
        $member->getStudentLicense()->setTitle('Student Update 1');

        $mapper->saveOne($member);

        /** @var StubMember $newMember */
        $newMember = $mapper->findOne(2);

        self::assertNull($newMember->getStudentLicense());

        self::assertEquals(
            '',
            self::$orm->from(StubLicense::class)
                ->where('id', 4)
                ->get()
                ->target_no
        );
    }

    public function testDelete()
    {
        $mapper = $this->createTestMapper();

        /** @var StubMember $member */
        $member = $mapper->findOne(3);

        $studentLicId = $member->getStudentLicense()->getId();
        $teacherLicId = $member->getTeacherLicense()->getId();

        $mapper->deleteWhere($member);

        self::assertEquals(6, $studentLicId);
        self::assertEquals(5, $teacherLicId);
        self::assertNull(
            self::$orm->findOne(StubLicense::class, 5)
        );
        self::assertNull(
            self::$orm->findOne(StubLicense::class, 6)
        );
    }

    public function createTestMapper(
        string $onUpdate = Action::CASCADE,
        string $onDelete = Action::CASCADE,
        bool $flush = false
    ) {
        $mapper = self::$orm->mapper(StubMember::class);
        $mapper->getMetadata()
            ->getRelationManager()
            ->getRelation('studentLicense')
            ->flush($flush)
            ->onUpdate($onUpdate)
            ->onDelete($onDelete);
        $mapper->getMetadata()
            ->getRelationManager()
            ->getRelation('teacherLicense')
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
