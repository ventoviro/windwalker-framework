<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Test;

use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\SQLServerGrammar;

use function Windwalker\Query\qn;
use function Windwalker\raw;

/**
 * The SqlsrvQueryTest class.
 */
class SQLServerQueryTest extends QueryTest
{
    protected static array $nameQuote = ['[', ']'];

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function createGrammar(): AbstractGrammar
    {
        return new SQLServerGrammar();
    }

    /**
     * testParseJsonSelector
     *
     * @param  string  $selector
     * @param  bool    $unQuoteLast
     * @param  string  $expected
     *
     * @return  void
     *
     * @dataProvider parseJsonSelectorProvider
     */
    public function testParseJsonSelector(string $selector, bool $unQuoteLast, string $expected)
    {
        $parsed = $this->instance->jsonSelector($selector);

        $bounded = $this->instance->getMergedBounded();

        self::assertEquals(
            $expected,
            BoundedHelper::emulatePrepared(
                $this->instance->getEscaper(),
                (string) $parsed,
                $bounded
            )
        );
        $this->instance->render(true);
    }

    public function parseJsonSelectorProvider(): array
    {
        return [
            [
                'foo ->> bar',
                true,
                'JSON_VALUE([foo], \'$.bar\')',
            ],
            [
                'foo->bar[1]->>yoo',
                true,
                'JSON_VALUE([foo], \'$.bar[1].yoo\')',
            ],
            [
                'foo->bar[1]->>\'yoo\'',
                true,
                'JSON_VALUE([foo], \'$.bar[1].yoo\')',
            ],
            [
                'foo->bar[1]->>"yoo"',
                true,
                'JSON_VALUE([foo], \'$.bar[1]."yoo"\')',
            ],
            [
                'foo->bar[1]->\'yoo\'',
                false,
                'JSON_QUERY([foo], \'$.bar[1].yoo\')',
            ],
        ];
    }

    public function testJsonQuote(): void
    {
        $query = $this->instance->select('foo -> bar ->> yoo AS yoo')
            ->selectRaw('%n AS l', 'foo -> bar -> loo')
            ->from('test')
            ->where('foo -> bar ->> yoo', 'www')
            ->having('foo -> bar', '=', qn('hoo -> joo ->> moo'))
            ->order('foo -> bar ->> yoo', 'DESC');

        self::assertSqlEquals(
            <<<SQL
            SELECT JSON_VALUE([foo], '$.bar.yoo') AS [yoo], JSON_QUERY([foo], '$.bar.loo') AS l
            FROM [test] WHERE JSON_VALUE([foo], '$.bar.yoo') = 'www'
            HAVING JSON_QUERY([foo], '$.bar') = JSON_VALUE([hoo], '$.joo.moo')
            ORDER BY JSON_VALUE([foo], '$.bar.yoo') DESC
            SQL,
            $query->render(true)
        );
    }

    public function testInsert(): void
    {
        $this->instance->insert('foo', true)
            ->columns('id', 'title', ['foo', 'bar'], 'yoo')
            ->values(
                [1, 'A', 'a', null, raw('CURRENT_TIMESTAMP()')],
                [2, 'B', 'b', null, raw('CURRENT_TIMESTAMP()')],
                [3, 'C', 'c', null, raw('CURRENT_TIMESTAMP()')]
            );

        self::assertSqlEquals(
            <<<SQL
SET IDENTITY_INSERT "foo" ON;
INSERT INTO "foo"
("id", "title", "foo", "bar", "yoo")
VALUES
    (1, 'A', 'a', NULL, CURRENT_TIMESTAMP()),
    (2, 'B', 'b', NULL, CURRENT_TIMESTAMP()),
    (3, 'C', 'c', NULL, CURRENT_TIMESTAMP())
; SET IDENTITY_INSERT "foo" OFF;
SQL
            ,
            $this->instance
        );

        $q = self::createQuery()
            ->insert('foo')
            ->set('id', 1)
            ->set(
                [
                    'title' => 'A',
                    'foo' => 'a',
                    'bar' => null,
                    'yoo' => raw('CURRENT_TIMESTAMP()'),
                ]
            );

        self::assertSqlEquals(
            <<<SQL
INSERT INTO "foo" SET "id" = 1, "title" = 'A', "foo" = 'a', "bar" = NULL, "yoo" = CURRENT_TIMESTAMP()
SQL
            ,
            $q
        );

        $q = self::createQuery()
            ->insert('foo')
            ->columns('id', 'title')
            ->values(
                self::createQuery()
                    ->select('id', 'title')
                    ->from('articles'),
                self::createQuery()
                    ->select('id', 'title')
                    ->from('categories')
            );

        self::assertSqlEquals(
            <<<SQL
INSERT INTO "foo" ("id", "title")
(SELECT "id", "title" FROM "articles") UNION (SELECT "id", "title" FROM "categories")
SQL
            ,
            $q
        );
    }

    public function testLimitOffset()
    {
        // Limit
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->limit(5);

        self::assertSqlEquals(
            'SELECT TOP 5 * FROM "foo" ORDER BY "id"',
            $q->render()
        );

        // Offset
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->offset(10);

        // Only offset will not work
        self::assertSqlEquals(
        // phpcs:disable
            'SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS RowNumber FROM ( SELECT * FROM [foo] ORDER BY [id] ) AS A) AS A WHERE RowNumber > 10',
            // phpcs:enable
            $q->render()
        );

        // Limit & Offset
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->limit(5)
            ->offset(15);

        self::assertSqlEquals(
        // phpcs:disable
            'SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS RowNumber FROM ( SELECT TOP 20 * FROM [foo] ORDER BY [id] ) AS A) AS A WHERE RowNumber > 15',
            // phpcs:enable
            $q->render()
        );
    }
}
