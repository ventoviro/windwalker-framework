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
 * The PdoSqlsrvConnection class.
 */
class PdoSqlsrvConnection extends AbstractPdoConnection
{
    protected static string $dbtype = 'sqlsrv';

    protected static array $defaultAttributes = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_STRINGIFY_FETCHES => false,

        // @see \PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE
        1005 => true,
    ];

    public static function getParameters(array $options): array
    {
        $params['Server'] = $options['host'];

        if (isset($params['port'])) {
            $params['Server'] .= ',' . $params['port'];
        }

        $params['Database']     = $options['dbname'] ?? null;
        $params['CharacterSet'] = $options['charset'] ?? null;
        $params['MultipleActiveResultSets'] = 'False';

        $options['dsn'] = static::getDsn($params);

        return $options;
    }
}
