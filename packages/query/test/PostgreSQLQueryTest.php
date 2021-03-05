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
use Windwalker\Query\Grammar\PostgreSQLGrammar;

use function Windwalker\Query\qn;

/**
 * The PostgresqlQueryTest class.
 */
class PostgreSQLQueryTest extends QueryTest
{
    protected function setUp(): void
    {
        parent::setUp();
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
                '"foo"->>\'bar\''
            ],
            [
                'foo->bar[1]->>yoo',
                true,
                '"foo"->\'bar\'->1->>\'yoo\''
            ],
            [
                'foo->bar[1]->>\'yoo\'',
                true,
                '"foo"->\'bar\'->1->>\'yoo\''
            ],
            [
                'foo->bar[1]->\'yoo\'',
                false,
                '"foo"->\'bar\'->1->\'yoo\''
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
            SELECT "foo"->'bar'->>'yoo' AS "yoo", "foo"->'bar'->'loo' AS l
            FROM "test"
            WHERE "foo"->'bar'->>'yoo' = 'www'
            HAVING "foo"->'bar' = "hoo"->'joo'->>'moo'
            ORDER BY "foo"->'bar'->>'yoo' DESC
            SQL,
            $query->render(true)
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
            'SELECT * FROM "foo" ORDER BY "id" LIMIT 5',
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
            'SELECT * FROM "foo" ORDER BY "id" OFFSET 10',
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
            'SELECT * FROM "foo" ORDER BY "id" LIMIT 5 OFFSET 15',
            $q->render()
        );
    }

    public static function createGrammar(): AbstractGrammar
    {
        return new PostgreSQLGrammar();
    }
}
