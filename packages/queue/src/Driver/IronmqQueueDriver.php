<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use DomainException;
use IronMQ\IronMQ;
use Windwalker\Queue\QueueMessage;

/**
 * The IronmqQueueDriver class.
 *
 * @since  3.2
 */
class IronmqQueueDriver implements QueueDriverInterface
{
    /**
     * Property client.
     *
     * @var  IronMQ
     */
    protected $client;

    /**
     * Property channel.
     *
     * @var  string
     */
    protected $channel;

    /**
     * IronmqQueueDriver constructor.
     *
     * @param  string  $projectId
     * @param  string  $token
     * @param  string  $channel
     * @param  array   $options
     */
    public function __construct(string $projectId, string $token, string $channel, array $options = [])
    {
        $this->client = $this->getIronMQ($projectId, $token, $options);

        $this->channel = $channel;
    }

    /**
     * push
     *
     * @param  QueueMessage  $message
     *
     * @return string
     */
    public function push(QueueMessage $message): string
    {
        $channel = $message->getChannel() ?: $this->channel;

        $options = $message->getOptions();

        $options['delay'] = $message->getDelay();

        return (string) $this->client->postMessage($channel, json_encode($message), $options)->id;
    }

    /**
     * pop
     *
     * @param  string|null  $channel
     *
     * @return QueueMessage|null
     */
    public function pop(?string $channel = null): ?QueueMessage
    {
        $channel = $channel ?: $this->channel;

        $result = $this->client->reserveMessage($channel);

        if (!$result) {
            return null;
        }

        $message = new QueueMessage();

        $message->setId($result->id);
        $message->setAttempts($result->reserved_count);
        $message->setBody(json_decode($result->body, true));
        $message->setRawBody($result->body);
        $message->setChannel($channel ?: $this->channel);
        $message->set('reservation_id', $result->reservation_id);

        return $message;
    }

    /**
     * delete
     *
     * @param  QueueMessage  $message
     *
     * @return IronmqQueueDriver
     */
    public function delete(QueueMessage $message): static
    {
        $channel = $message->getChannel() ?: $this->channel;

        $this->client->deleteMessage($channel, $message->getId(), $message->get('reservation_id'));

        return $this;
    }

    /**
     * release
     *
     * @param  QueueMessage|string  $message
     *
     * @return static
     */
    public function release(QueueMessage $message): static
    {
        $channel = $message->getChannel() ?: $this->channel;

        $this->client->releaseMessage(
            $channel,
            $message->getId(),
            $message->get('reservation_id'),
            $message->getDelay()
        );

        return $this;
    }

    /**
     * getIronMQ
     *
     * @param         $projectId
     * @param         $token
     * @param  array  $options
     *
     * @return  IronMQ
     */
    public function getIronMQ($projectId, $token, array $options): IronMQ
    {
        if (!class_exists(IronMQ::class)) {
            throw new DomainException('Please install iron-io/iron_mq first.');
        }

        $defaultOptions = [
            'project_id' => $projectId,
            'token' => $token,
        ];

        $options = array_merge($defaultOptions, $options);

        return new IronMQ($options);
    }
}
