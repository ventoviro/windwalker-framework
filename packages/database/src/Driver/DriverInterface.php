<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver;

use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Query\Query;

/**
 * Interface DriverInterface
 */
interface DriverInterface
{
    public function isSupported(): bool;

    /**
     * connect
     *
     * @return  ConnectionInterface
     */
    public function getConnection(): ConnectionInterface;

    /**
     * Use a connection then auto release.
     *
     * @param  callable  $callback
     *
     * @return  mixed
     */
    public function useConnection(callable $callback): mixed;

    /**
     * disconnect
     *
     * @return  int
     */
    public function disconnectAll(): int;

    /**
     * Prepare a statement.
     *
     * @param  string|Query  $query
     * @param  array         $options
     *
     * @return  StatementInterface
     */
    public function prepare(mixed $query, array $options = []): StatementInterface;

    /**
     * Execute a query.
     *
     * @param  string|Query  $query
     * @param  array|null    $params
     *
     * @return StatementInterface
     */
    public function execute(mixed $query, ?array $params = null): StatementInterface;

    /**
     * Quote and escape a value.
     *
     * @param  string  $value
     *
     * @return  string
     */
    public function quote(string $value): string;

    /**
     * Escape a value.
     *
     * @param  string  $value
     *
     * @return  string
     */
    public function escape(string $value): string;

    /**
     * getVersion
     *
     * @return  string
     */
    public function getVersion(): string;

    public function getPlatformName(): string;

    public function getPlatform(): AbstractPlatform;
}
