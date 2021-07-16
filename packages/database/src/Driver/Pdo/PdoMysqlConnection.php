<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

/**
 * The MysqlConnection class.
 */
class PdoMysqlConnection extends AbstractPdoConnection
{
    protected static string $dbtype = 'mysql';

    public static function getParameters(array $options): array
    {
        $params['host'] = $options['host'] ?? null;
        $params['port'] = $options['port'] ?? null;
        $params['unix_socket'] = $options['unix_socket'] ?? null;
        $params['dbname'] = $options['dbname'] ?? null;
        $params['charset'] = $options['charset'] ?? 'utf8';

        $options['dsn'] ??= static::getDsn($params);

        if (strtolower($params['charset']) === 'utf8') {
            $options['driverOptions'][\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
        }

        return $options;
    }
}
