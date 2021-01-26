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
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Platform\PostgreSQLPlatform;

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
    public function lastInsertId(?string $sequence = null): ?string
    {
        /** @var PostgreSQLPlatform $platform */
        $platform = $this->getPlatform();

        return $platform->lastInsertId($this->lastQuery, $sequence);
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
        return pg_escape_string($this->getConnection()->get(), $value);
    }

    /**
     * getVersion
     *
     * @return  string
     */
    public function getVersion(): string
    {
        return pg_version($this->getConnection()->get())['server'] ?? '';
    }
}
