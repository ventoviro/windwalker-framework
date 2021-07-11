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

use function Windwalker\raw;

/**
 * The PostgreSQLTrait class.
 */
class PostgreSQLReseter extends AbstractReseter
{
    protected static string $platform = 'PostgreSQL';

    public function createDatabase(PDO $pdo, string $dbname): void
    {
        $dbs = $pdo->query(
            $this->createQuery()
                ->select('datname')
                ->from('pg_database')
                ->where('datistemplate', raw('false'))
                ->render(true)
        )
            ->fetchAll(PDO::FETCH_COLUMN) ?: [];

        if (!in_array($dbname, $dbs, true)) {
            $pdo->exec('CREATE DATABASE ' . static::qn($dbname));
        }
    }

    public function clearAllTables(PDO $pdo, string $dbname): void
    {
        // Drop Tables
        $tables = $pdo->query(
            $this->createQuery()
                ->select('table_name AS Name')
                ->from('information_schema.tables')
                ->where('table_type', 'BASE TABLE')
                ->order('table_name', 'ASC')
                ->whereNotIn('table_schema', ['pg_catalog', 'information_schema'])
                ->render(true)
        )->fetchAll(PDO::FETCH_COLUMN) ?: [];

        if ($tables) {
            foreach ($tables as $table) {
                $pdo->exec(
                    $this->createQuery()->format(
                        'DROP TABLE IF EXISTS %n CASCADE',
                        $table
                    )
                );
            }
        }

        // Drop Views
        $tables = $pdo->query(
            $this->createQuery()
                ->select('table_name AS Name')
                ->from('information_schema.tables')
                ->where('table_type', 'VIEW')
                ->order('table_name', 'ASC')
                ->whereNotIn('table_schema', ['pg_catalog', 'information_schema'])
                ->render(true)
        )->fetchAll(PDO::FETCH_COLUMN) ?: [];

        if ($tables) {
            foreach ($tables as $table) {
                $pdo->exec(
                    $this->createQuery()->format(
                        'DROP VIEW %n CASCADE',
                        $table
                    )
                );
            }
        }
    }
}
