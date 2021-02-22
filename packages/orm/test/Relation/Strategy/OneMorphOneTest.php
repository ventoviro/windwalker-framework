<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Relation\Strategy;

use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Test\AbstractORMTestCase;
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

        show($studentLicense);
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
