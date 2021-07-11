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
 * The PdoHelper class.
 */
class DsnHelper
{
    /**
     * extractDsn
     *
     * @param  string  $dsn
     *
     * @return  array
     */
    public static function extract(string $dsn): array
    {
        // Parse DSN to array
        $dsn = str_replace(';', "\n", $dsn);

        $values = [];

        foreach (explode("\n", $dsn) as $value) {
            [$k, $v] = explode('=', trim($value));

            $values[$k] = $v;
        }

        return $values;
    }

    public static function build(array $params, ?string $dbtype = null, string $delimiter = ';'): string
    {
        $params = array_filter($params);

        $dsn = [];

        foreach ($params as $key => $value) {
            $dsn[] = $key . '=' . $value;
        }

        $dsn = implode($delimiter, $dsn);

        if ($dbtype) {
            $dsn = $dbtype . ':' . $dsn;
        }

        return $dsn;
    }
}
