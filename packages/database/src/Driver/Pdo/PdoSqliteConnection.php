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
 * The PdoSqliteConnection class.
 */
class PdoSqliteConnection extends AbstractPdoConnection
{
    protected static string $dbtype = 'sqlite';

    public static function getParameters(array $options): array
    {
        $options['dsn'] ??= static::$dbtype . ':' . ($options['dbname'] ?? $options['file'] ?? ':memory:');

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): mixed
    {
        return parent::disconnect();
    }
}
