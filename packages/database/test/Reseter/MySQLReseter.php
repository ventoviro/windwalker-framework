<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Reseter;

use PDO;

/**
 * The AbstractMySQLTestCase class.
 */
class MySQLReseter extends AbstractReseter
{
    protected static string $platform = 'MySQL';

    public function createDatabase(PDO $pdo, string $dbname): void
    {
        $pdo->exec('DROP DATABASE IF EXISTS ' . static::qn($dbname));

        // DatabaseManager created
        $pdo->exec('DROP DATABASE IF EXISTS ' . static::qn($dbname . '_new'));

        $pdo->exec('CREATE DATABASE ' . static::qn($dbname));
    }

    public function clearAllTables(PDO $pdo, string $dbname): void
    {
        // Drop Tables
        $tables = $pdo->query(
            $this->createQuery()
                ->select('TABLE_NAME')
                ->from('INFORMATION_SCHEMA.TABLES')
                ->where('TABLE_TYPE', 'BASE TABLE')
                ->where('TABLE_SCHEMA', '=', $dbname)
                ->render(true)
        )->fetchAll(PDO::FETCH_COLUMN) ?: [];

        if ($tables) {
            foreach ($tables as $table) {
                $pdo->exec('DROP TABLE IF EXISTS ' . $table);
            }
        }

        // Drop Views
        $tables = $pdo->query(
            $this->createQuery()
                ->select('TABLE_NAME')
                ->from('INFORMATION_SCHEMA.TABLES')
                ->where('TABLE_TYPE', 'VIEW')
                ->where('TABLE_SCHEMA', '=', $dbname)
                ->render(true)
        )->fetchAll(PDO::FETCH_COLUMN) ?: [];

        if ($tables) {
            foreach ($tables as $table) {
                $pdo->exec('DROP VIEW IF EXISTS ' . $table);
            }
        }
    }
}
