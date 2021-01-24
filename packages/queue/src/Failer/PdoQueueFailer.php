<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Failer;

/**
 * The DatabaseQueueFailer class.
 *
 * @since  3.3
 */
class PdoQueueFailer implements QueueFailerInterface
{
    /**
     * Property db.
     *
     * @var  \PDO
     */
    protected \PDO $pdo;

    /**
     * Property table.
     *
     * @var  string
     */
    protected string $table;

    /**
     * DatabaseQueueFailer constructor.
     *
     * @param \PDO   $pdo
     * @param string $table
     */
    public function __construct(\PDO $pdo, string $table = 'queue_failed_jobs')
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    /**
     * isSupported
     *
     * @return  bool
     */
    public function isSupported(): bool
    {
        $sql = 'SHOW TABLES LIKE :table';

        $stat = $this->pdo->prepare($sql);
        $stat->bindValue(':table', $this->table);
        $stat->execute();

        return (bool) $stat->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * add
     *
     * @param  string  $connection
     * @param  string  $channel
     * @param  string  $body
     * @param  string  $exception
     *
     * @return  int|string
     */
    public function add(string $connection, string $channel, string $body, string $exception): int|string
    {
        // For B/C
        $created = (new \DateTime('now'))->format('Y-m-d H:i:s');

        $sql = 'INSERT INTO ' . $this->table .
            ' (connection, channel, body, exception, created)' .
            ' VALUES (:connection, :channel, :body, :exception, :created)';

        $stat = $this->pdo->prepare($sql);
        $stat->bindValue(':connection', $connection);
        $stat->bindValue(':channel', $channel);
        $stat->bindValue(':body', $body);
        $stat->bindValue(':exception', $exception);
        $stat->bindValue(':created', $created);

        $stat->execute();

        return $this->pdo->lastInsertId();
    }

    /**
     * all
     *
     * @return  array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function all(): array
    {
        $sql = 'SELECT * FROM ' . $this->table;

        $stat = $this->pdo->query($sql);

        return $stat->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * get
     *
     * @param  mixed  $conditions
     *
     * @return array|null
     */
    public function get(mixed $conditions): ?array
    {
        $sql = 'SELECT * FROM ' . $this->table .
            ' WHERE id = :id';

        $stat = $this->pdo->prepare($sql);
        $stat->bindValue(':id', $conditions);
        $stat->execute();

        return $stat->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * remove
     *
     * @param  mixed  $conditions
     *
     * @return  bool
     */
    public function remove(mixed $conditions): bool
    {
        $sql = 'DELETE FROM ' . $this->table .
            ' WHERE id = :id';

        $stat = $this->pdo->prepare($sql);
        $stat->bindValue(':id', $conditions);

        return $stat->execute();
    }

    /**
     * clear
     *
     * @return  bool
     */
    public function clear(): bool
    {
        $sql = 'TRUNCATE TABLE ' . $this->table;

        return $this->pdo->prepare($sql)->execute();
    }

    /**
     * Method to get property Table
     *
     * @return  string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Method to set property table
     *
     * @param   string $table
     *
     * @return  static  Return self to support chaining.
     */
    public function setTable(string $table): static
    {
        $this->table = $table;

        return $this;
    }
}
