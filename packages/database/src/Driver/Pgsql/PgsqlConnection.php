<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pgsql;

use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Driver\Pdo\DsnHelper;
use Windwalker\Database\Exception\DatabaseConnectException;

/**
 * The PgsqlConnection class.
 */
class PgsqlConnection extends AbstractConnection
{
    protected static string $name = 'pgsql';

    /**
     * @inheritDoc
     */
    public static function isSupported(): bool
    {
        return extension_loaded('pgsql');
    }

    public static function getParameters(array $options): array
    {
        $params = [];

        $params['host'] = $options['host'];
        $params['port'] = $options['port'] ?? null;
        $params['dbname'] = $options['database'] ?? null;
        $params['user'] = $options['username'] ?? null;
        $params['password'] = $options['password'] ?? null;

        if (isset($options['charset'])) {
            $params['options'] = sprintf(
                "'--client_encoding=%s'",
                strtoupper($options['charset'])
            );
        }

        $options['params'] = DsnHelper::build($params, null, ' ');

        return $options;
    }

    protected function doConnect(array $options)
    {
        $res = @pg_connect($options['params']);

        if (!$res) {
            throw new DatabaseConnectException('Unable to connect to pgsql.');
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): mixed
    {
        if (!$this->isConnected()) {
            return true;
        }

        $r = pg_close($this->connection);

        $this->connection = null;

        return $r;
    }
}
