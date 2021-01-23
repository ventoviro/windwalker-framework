<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Sqlsrv;

use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Exception\DatabaseConnectException;

/**
 * The SqlsrvConnection class.
 */
class SqlsrvConnection extends AbstractConnection
{
    protected static string $name = 'sqlsrv';

    /**
     * @inheritDoc
     */
    public static function isSupported(): bool
    {
        return extension_loaded('sqlsrv');
    }

    public static function getParameters(array $options): array
    {
        $params = [];

        $params['Database']     = $options['database'] ?? null;
        $params['UID']          = $options['username'] ?? null;
        $params['PWD']          = $options['password'] ?? null;
        $params['CharacterSet'] = $options['charset'] ?? null;

        $params            = array_filter($params);
        $options['params'] = $params;

        return $options;
    }

    protected function doConnect(array $options)
    {
        $conn = sqlsrv_connect(
            $options['host'],
            $options['params']
        );

        if (!$conn) {
            $errors = sqlsrv_errors();

            throw new DatabaseConnectException(
                sprintf(
                    'SQLSTATE: %s Message: %s',
                    $errors[0]['SQLSTATE'],
                    $errors[0]['message']
                ),
                $errors[0]['code']
            );
        }

        return $conn;
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): mixed
    {
        if (!$this->isConnected()) {
            return true;
        }

        $r = sqlsrv_close($this->connection);

        $this->connection = null;

        return $r;
    }
}
