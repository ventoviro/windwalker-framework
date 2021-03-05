<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Sqlsrv;

use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Query\Grammar\SQLiteGrammar;

/**
 * The SqlsrvDriver class.
 */
class SqlsrvDriver extends AbstractDriver
{
    protected static string $name = 'sqlsrv';

    protected string $platformName = 'sqlsrv';

    /**
     * @inheritDoc
     */
    public function createStatement(string $query, array $bounded = [], array $options = []): StatementInterface
    {
        return new SqlsrvStatement($this, $query, $bounded, $options);
    }

    /**
     * @inheritDoc
     */
    public function quote(string $value): string
    {
        return "'" . $this->escape($value) . "'";
    }

    /**
     * @inheritDoc
     */
    public function escape(string $value): string
    {
        return SQLiteGrammar::localEscape($value);
    }

    /**
     * getVersion
     *
     * @return  string
     */
    public function getVersion(): string
    {
        return (string) (sqlsrv_server_info($this->getConnection()->get())['SQLServerVersion'] ?? '');
    }
}
