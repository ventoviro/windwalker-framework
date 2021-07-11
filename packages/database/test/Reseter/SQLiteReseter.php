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
 * The SQLIteReseter class.
 */
class SQLiteReseter extends AbstractReseter
{
    protected static string $platform = 'SQLite';

    public function createDatabase(PDO $pdo, string $dbname): void
    {
        if ($dbname !== ':memory:' && is_file($dbname)) {
            @unlink($dbname);
        }
    }

    public function clearAllTables(PDO $pdo, string $dbname): void
    {
        // Drop Tables
        $tables = $pdo->query(
            $this->createQuery()
                ->select('name')
                ->from('sqlite_master')
                ->where('type', 'table')
                ->where('name', 'not like', 'sqlite_%')
                ->render(true)
        )->fetchAll(PDO::FETCH_COLUMN) ?: [];

        if ($tables) {
            foreach ($tables as $table) {
                $pdo->exec(
                    $this->createQuery()->format(
                        'DROP TABLE IF EXISTS %n',
                        $table
                    )
                );
            }
        }

        // Drop Views
        $tables = $pdo->query(
            $this->createQuery()
                ->select('name')
                ->from('sqlite_master')
                ->where('type', 'view')
                ->where('name', 'not like', 'sqlite_%')
                ->render(true)
        )->fetchAll(PDO::FETCH_COLUMN) ?: [];

        if ($tables) {
            foreach ($tables as $table) {
                $pdo->exec(
                    $this->createQuery()->format(
                        'DROP VIEW %n',
                        $table
                    )
                );
            }
        }
    }
}
