<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Windwalker\Queue\QueueMessage;

/**
 * The RabbitmqQueueDriver class.
 *
 * @since  3.2
 */
class RabbitmqQueueDriver implements QueueDriverInterface
{
    /**
     * Property client.
     *
     * @var  AMQPStreamConnection
     */
    protected $client;

    /**
     * Property channel.
     *
     * @var  string
     */
    protected $channel;

    /**
     * Property channel.
     *
     * @var  AMQPChannel
     */
    protected $channel;

    /**
     * RabbitmqQueueDriver constructor.
     *
     * @param string $channel
     * @param array  $options
     */
    public function __construct(string $channel, array $options = [])
    {
        $this->client = $this->getAMQPConnection($options);

        $this->channel = $channel;
        $this->channel = $this->client->channel();
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

        $this->channelDeclare($channel);

        $options = [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ];

        if ($message->getDelay() > 0) {
            $options['application_headers'] = new AMQPTable(
                [
                    'x-delay' => $message->getDelay(),
                ]
            );
        }

        $msg = new AMQPMessage(json_encode($message), $options);

        $this->channel->basic_publish($msg, '', $channel);

        return '1';
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

        $this->channelDeclare($channel);

        $this->channel->basic_qos(null, 1, null);
        $result = $this->channel->basic_get($channel, false);

        if (!$result) {
            return null;
        }

        $message = new QueueMessage();

        $message->setId(0);
        $message->setBody(json_decode($result->body, true));
        $message->setRawBody($result->body);
        $message->setChannel($channel ?: $this->channel);

        // Delivery tag
        $message->set('delivery_tag', $result->delivery_info['delivery_tag']);

        // Attempts
        $attempts = $message->get('attempts', 0) + 1;

        $message->setAttempts($attempts);
        $message->set('attempts', $attempts);

        return $message;
    }

    /**
     * delete
     *
     * @param  QueueMessage  $message
     *
     * @return RabbitmqQueueDriver
     */
    public function delete(QueueMessage $message): static
    {
        $this->channel->basic_ack($message->get('delivery_tag'));

        return $this;
    }

    /**
     * release
     *
     * @param QueueMessage|string $message
     *
     * @return static
     */
    public function release(QueueMessage $message): static
    {
        $this->delete($message);

        $message->set('delivery_tag', null);

        $this->push($message);

        return $this;
    }

    /**
     * channelDeclare
     *
     * @param string $channel
     *
     * @return  void
     */
    protected function channelDeclare(string $channel)
    {
        $this->channel->channel_declare($channel, false, true, false, false);
    }

    /**
     * getAMQPConnection
     *
     * @param array $options
     *
     * @return  AMQPStreamConnection
     * @throws \DomainException
     */
    public function getAMQPConnection(array $options): AMQPStreamConnection
    {
        if (!class_exists(AMQPStreamConnection::class)) {
            throw new \DomainException('Please install php-amqplib/php-amqplib first.');
        }

        $defaultOptions = [
            'host' => 'localhost',
            'port' => 5672,
            'user' => 'guest',
            'password' => 'guest',
        ];

        $options = array_merge($defaultOptions, $options);

        return new AMQPStreamConnection(
            $options['host'],
            $options['port'],
            $options['user'],
            $options['password']
        );
    }
}
