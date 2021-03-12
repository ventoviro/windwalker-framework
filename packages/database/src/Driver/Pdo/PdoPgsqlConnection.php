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
 * The PdoPgsqlConnection class.
 */
class PdoPgsqlConnection extends AbstractPdoConnection
{
    protected static string $dbtype = 'pgsql';

    public static function getParameters(array $options): array
    {
        $params['host'] = $options['host'];
        $params['port'] = $options['port'] ?? null;
        $params['dbname'] = $options['dbname'] ?? null;
        $params['charset'] = $options['charset'] ?? null;

        $options['dsn'] = static::getDsn($params);

        return $options;
    }
}
