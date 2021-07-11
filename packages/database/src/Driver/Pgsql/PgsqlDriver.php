<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pgsql;

use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Database\Driver\StatementInterface;

/**
 * The PgsqlDriver class.
 */
class PgsqlDriver extends AbstractDriver
{
    /**
     * @var string
     */
    protected static string $name = 'pgsql';

    /**
     * @var string
     */
    protected string $platformName = 'pgsql';

    /**
     * @inheritDoc
     */
    public function createStatement(string $query, array $bounded = [], array $options = []): StatementInterface
    {
        return new PgsqlStatement($this, $query, $bounded, $options);
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
            fn(ConnectionInterface $conn) => pg_escape_string($conn->get(), $value)
        );
    }

    /**
     * getVersion
     *
     * @return  string
     */
    public function getVersion(): string
    {
        return $this->useConnection(fn(ConnectionInterface $conn) => pg_version($conn->get())['server'] ?? '');
    }
}
