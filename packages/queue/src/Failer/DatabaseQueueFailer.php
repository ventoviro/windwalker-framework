<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Failer;

use DateTime;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use Windwalker\Database\DatabaseAdapter;

use function Windwalker\collect;

/**
 * The DatabaseQueueFailer class.
 *
 * @since  3.2
 */
class DatabaseQueueFailer implements QueueFailerInterface
{
    /**
     * Property db.
     *
     * @var  DatabaseAdapter
     */
    protected DatabaseAdapter $db;

    /**
     * Property table.
     *
     * @var  string
     */
    protected string $table;

    /**
     * DatabaseQueueFailer constructor.
     *
     * @param  DatabaseAdapter  $db
     * @param  string           $table
     */
    public function __construct(DatabaseAdapter $db, string $table = 'queue_failed_jobs')
    {
        $this->db = $db;
        $this->table = $table;
    }

    /**
     * isSupported
     *
     * @return  bool
     */
    public function isSupported(): bool
    {
        return $this->db->getTable($this->table)->exists();
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
     * @throws JsonException
     */
    public function add(string $connection, string $channel, string $body, string $exception): int|string
    {
        $data = compact(
            'connection',
            'channel',
            'body',
            'exception'
        );

        $data['created'] = new DateTime('now');

        $data = $this->db->getWriter()->insertOne($this->table, $data, 'id');

        return $data['id'];
    }

    /**
     * all
     *
     * @return  array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function all(): array
    {
        return $this->db->select('*')
            ->from($this->table)
            ->all()
            ->dump();
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
        $item = $this->db->select('*')
                ->from($this->table)
                ->where('id', $conditions)
                ->get() ?? collect();

        return $item->dump();
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
        $this->db->delete($this->table)
            ->where('id', $conditions)
            ->execute()
            ->countAffected();

        return true;
    }

    /**
     * clear
     *
     * @return  bool
     */
    public function clear(): bool
    {
        $this->db->getTable($this->table)->truncate();

        return true;
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
     * @param  string  $table
     *
     * @return  static  Return self to support chaining.
     */
    public function setTable(string $table): static
    {
        $this->table = $table;

        return $this;
    }
}
