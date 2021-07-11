<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Test;

use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\OracleGrammar;

/**
 * The OracleQueryTest class.
 */
class OracleQueryTest extends QueryTest
{
    public static function createGrammar(): AbstractGrammar
    {
        return new OracleGrammar();
    }

    public function testLimitOffset()
    {
        // Limit
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->limit(5);

        // phpcs:disable
        self::assertSqlEquals(
            'SELECT windwalker2.* FROM ( SELECT windwalker1.*, ROWNUM AS windwalker_db_rownum FROM ( SELECT * FROM "foo" ORDER BY "id" ) windwalker1 ) windwalker2 WHERE windwalker2.windwalker_db_rownum BETWEEN 1 AND 5',
            $q->render()
        );
        // phpcs:enable

        // Offset
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->offset(10);

        // phpcs:disable
        // Only offset will not work
        self::assertSqlEquals(
            'SELECT windwalker2.* FROM ( SELECT windwalker1.*, ROWNUM AS windwalker_db_rownum FROM ( SELECT * FROM "foo" ORDER BY "id" ) windwalker1 ) windwalker2 WHERE windwalker2.windwalker_db_rownum > 11',
            $q->render()
        );
        // phpcs:enable

        // Limit & Offset
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->limit(5)
            ->offset(15);

        // phpcs:disable
        self::assertSqlEquals(
            'SELECT windwalker2.* FROM ( SELECT windwalker1.*, ROWNUM AS windwalker_db_rownum FROM ( SELECT * FROM "foo" ORDER BY "id" ) windwalker1 ) windwalker2 WHERE windwalker2.windwalker_db_rownum BETWEEN 16 AND 20',
            $q->render()
        );
        // phpcs:enable
    }
}
