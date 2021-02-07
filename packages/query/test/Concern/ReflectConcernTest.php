<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Query\Test\Concern;

use PHPUnit\Framework\TestCase;
use Windwalker\Query\Clause\AsClause;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Classes\DocblockHelper;

/**
 * The ReflectConcernTest class.
 */
class ReflectConcernTest extends TestCase
{
    protected Query $instance;

    public function testGetAllTables()
    {
        $this->instance->select('*')
            ->from('users', 'u')
            ->leftJoin('comment', 'c', 'c.user_id', 'u.id')
            ->rightJoin('groups', 'g', 'g.id', 'u.group')
            ->leftJoin('socials', 's', 's.user_id', 'u.id');

        $tables = $this->instance->getAllTables();
        $flatItems = Arr::collapse($tables, true);

        self::assertEquals(
            [
                'u',
                'c',
                's',
                'g'
            ],
            array_map(
                [$this->instance, 'stripNameQuote'],
                array_keys(Arr::collapse($tables, true)),
            )
        );
        self::assertContainsOnlyInstancesOf(AsClause::class, $flatItems);
        self::assertEquals(
            [
                'FROM',
                'LEFT JOIN',
                'RIGHT JOIN'
            ],
            array_keys($tables)
        );
    }

    public static function createQuery(): Query
    {
        return new Query(new MockEscaper(), new BaseGrammar());
    }

    protected function setUp(): void
    {
        $this->instance = self::createQuery();
    }
}
