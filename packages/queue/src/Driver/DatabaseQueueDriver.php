<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use Windwalker\Database\DatabaseAdapter;
use Windwalker\Query\Query;
use Windwalker\Queue\QueueMessage;

/**
 * The DatabaseQueueDriver class.
 *
 * @since  3.2
 */
class DatabaseQueueDriver implements QueueDriverInterface
{
    /**
     * Property db.
     *
     * @var  DatabaseAdapter
     */
    protected DatabaseAdapter $db;

    protected string $table;

    protected string $channel;

    protected int $timeout;

    /**
     * DatabaseQueueDriver constructor.
     *
     * @param  DatabaseAdapter  $db
     * @param  string           $channel
     * @param  string           $table
     * @param  int              $timeout
     */
    public function __construct(
        DatabaseAdapter $db,
        string $channel = 'default',
        string $table = 'queue_jobs',
        int $timeout = 60
    ) {
        $this->db      = $db;
        $this->table   = $table;
        $this->channel = $channel;
        $this->timeout = $timeout;
    }

    /**
     * push
     *
     * @param  QueueMessage  $message
     *
     * @return int|string
     * @throws \Exception
     */
    public function push(QueueMessage $message): int|string
    {
        $time = new \DateTimeImmutable('now');

        $data = [
            'channel' => $message->getChannel() ?: $this->channel,
            'body' => json_encode($message, JSON_THROW_ON_ERROR),
            'attempts' => 0,
            'created' => $time->format('Y-m-d H:i:s'),
            'visibility' => $time->modify(sprintf('+%dseconds', $message->getDelay())),
            'reserved' => null,
        ];

        $data = $this->db->getWriter()->insertOne($this->table, $data, 'id');

        return $data['id'];
    }

    /**
     * pop
     *
     * @param  string|null  $channel
     *
     * @return QueueMessage|null
     * @throws \Throwable
     */
    public function pop(?string $channel = null): ?QueueMessage
    {
        $channel = $channel ?: $this->channel;

        $now = new \DateTimeImmutable('now');

        $query = $this->db->getQuery(true);

        $query->select('*')
            ->from($this->table)
            ->where('channel', $channel)
            ->where('visibility', '<=', $now)
            ->orWhere(
                function (Query $query) use ($now) {
                    $query->where('reserved', null)
                        ->where('reserved', '<', $now->modify('-' . $this->timeout . 'seconds'));
                }
            )
            ->forUpdate();

        $data = $this->db->transaction(
            function () use ($now, $query) {
                $data = $this->db->prepare($query)->get();

                if (!$data) {
                    return null;
                }

                $data['attempts']++;

                $values = ['reserved' => $now, 'attempts' => $data['attempts']];

                $this->db->getWriter()->updateBatch($this->table, $values, ['id' => $data['id']]);

                return $data;
            }
        );

        if ($data === null) {
            return null;
        }

        $message = new QueueMessage();

        $message->setId($data['id']);
        $message->setAttempts($data['attempts']);
        $message->setBody(json_decode($data['body'], true, 512, JSON_THROW_ON_ERROR));
        $message->setRawBody($data['body']);
        $message->setChannel($channel);

        return $message;
    }

    /**
     * delete
     *
     * @param  QueueMessage  $message
     *
     * @return static
     */
    public function delete(QueueMessage $message): static
    {
        $channel = $message->getChannel() ?: $this->channel;

        $this->db->delete($this->table)
            ->where('id', $message->getId())
            ->where('channel', $channel)
            ->execute();

        return $this;
    }

    /**
     * release
     *
     * @param  QueueMessage|string  $message
     *
     * @return static
     * @throws \Exception
     */
    public function release(QueueMessage $message): static
    {
        $channel = $message->getChannel() ?: $this->channel;

        $time = new \DateTimeImmutable('now');
        $time = $time->modify('+' . $message->getDelay() . 'seconds');

        $values = [
            'reserved' => null,
            'visibility' => $time,
        ];

        $this->db->getWriter()->updateBatch(
            $this->table,
            $values,
            [
                'id' => $message->getId(),
                'channel' => $channel,
            ]
        );

        return $this;
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
     * @param  mixed  $table
     *
     * @return  static  Return self to support chaining.
     */
    public function setTable(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Method to get property Db
     *
     * @return  DatabaseAdapter
     */
    public function getDb(): DatabaseAdapter
    {
        return $this->db;
    }

    /**
     * Method to set property db
     *
     * @param  DatabaseAdapter  $db
     *
     * @return  static  Return self to support chaining.
     */
    public function setDb(DatabaseAdapter $db): static
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Reconnect database to avoid long connect issues.
     *
     * @return  static
     */
    public function reconnect(): static
    {
        $this->disconnect();

        $this->db->connect();

        return $this;
    }

    /**
     * Disconnect DB.
     *
     * @return  static
     *
     * @since  3.5.2
     */
    public function disconnect(): static
    {
        $this->db->disconnect();

        return $this;
    }
}
