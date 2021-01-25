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
    public function doPrepare(string $query, array $bounded = [], array $options = []): StatementInterface
    {
        $conn = $this->connect()->get();

        return new SqlsrvStatement($conn, $query, $bounded);
    }

    /**
     * @inheritDoc
     */
    public function lastInsertId(?string $sequence = null): ?string
    {
        return $this->prepare('SELECT @@IDENTITY')->result();
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
        return $this->getPlatform()->getGrammar()->localEscape($value);
    }

    /**
     * getVersion
     *
     * @return  string
     */
    public function getVersion(): string
    {
        return (string) (sqlsrv_server_info($this->connect()->get())['SQLServerVersion'] ?? '');
    }
}
