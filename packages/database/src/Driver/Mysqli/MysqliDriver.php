<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Mysqli;

use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Driver\TransactionDriverInterface;

/**
 * The MysqliDriver class.
 */
class MysqliDriver extends AbstractDriver implements TransactionDriverInterface
{
    protected static string $name = 'mysqli';

    /**
     * @var string
     */
    protected string $platformName = 'mysql';

    /**
     * @inheritDoc
     */
    public function doPrepare(string $query, array $bounded = [], array $options = []): StatementInterface
    {
        $conn = $this->getConnection()->get();

        return new MysqliStatement($conn, $query, $bounded);
    }

    /**
     * @inheritDoc
     */
    public function lastInsertId(?string $sequence = null): ?string
    {
        /** @var \mysqli $mysqli */
        $mysqli = $this->getConnection()->get();

        return (string) $mysqli->insert_id;
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
        /** @var \mysqli $mysqli */
        $mysqli = $this->getConnection()->get();

        return $mysqli->real_escape_string($value);
    }

    /**
     * @inheritDoc
     */
    public function transactionStart(): bool
    {
        /** @var \mysqli $mysqli */
        $mysqli = $this->getConnection()->get();

        return $mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
    }

    /**
     * @inheritDoc
     */
    public function transactionCommit(): bool
    {
        /** @var \mysqli $mysqli */
        $mysqli = $this->getConnection()->get();

        return $mysqli->commit();
    }

    /**
     * @inheritDoc
     */
    public function transactionRollback(): bool
    {
        /** @var \mysqli $mysqli */
        $mysqli = $this->getConnection()->get();

        return $mysqli->rollback();
    }

    /**
     * getVersion
     *
     * @return  string
     */
    public function getVersion(): string
    {
        /** @var \mysqli $mysqli */
        $mysqli = $this->getConnection()->get();

        return (string) $mysqli->server_version;
    }
}
