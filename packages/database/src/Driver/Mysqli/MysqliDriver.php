<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Mysqli;

use mysqli;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\ConnectionInterface;
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
     * @var ?ConnectionInterface
     */
    protected ?ConnectionInterface $connection = null;

    /**
     * @inheritDoc
     */
    public function createStatement(string $query, array $bounded = [], array $options = []): StatementInterface
    {
        return new MysqliStatement($this, $query, $bounded, $options);
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
        return $this->useConnection(
            function (ConnectionInterface $conn) use ($value) {
                /** @var mysqli $mysqli */
                $mysqli = $conn->get();

                return $mysqli->real_escape_string($value);
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function getConnection(): ConnectionInterface
    {
        if ($this->connection) {
            return $this->connection;
        }

        return parent::getConnection();
    }

    /**
     * @inheritDoc
     */
    public function transactionStart(): bool
    {
        /** @var mysqli $mysqli */
        $mysqli = $this->getConnection()->get();

        return $mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
    }

    /**
     * @inheritDoc
     */
    public function transactionCommit(): bool
    {
        /** @var mysqli $mysqli */
        $mysqli = $this->getConnection()->get();

        return $mysqli->commit();
    }

    /**
     * @inheritDoc
     */
    public function transactionRollback(): bool
    {
        /** @var mysqli $mysqli */
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
        return $this->useConnection(fn(ConnectionInterface $conn) => $conn->get()->server_version);
    }
}
