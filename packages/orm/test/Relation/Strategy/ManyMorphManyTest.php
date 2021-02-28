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
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\StubAction;
use Windwalker\ORM\Test\Entity\StubMember;
use Windwalker\ORM\Test\Entity\StubSakura;
use Windwalker\ORM\Test\Entity\StubSakuraRoseMap;

/**
 * The ManyMorphMany class.
 */
class ManyMorphManyTest extends AbstractORMTestCase
{
    public function testLoad()
    {

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
