<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use Aws\Sqs\SqsClient;
use DomainException;
use JsonException;
use Windwalker\Queue\QueueMessage;

/**
 * The SqsQueueDriver class.
 *
 * @since  3.2
 */
class SqsQueueDriver implements QueueDriverInterface
{
    /**
     * Property client.
     *
     * @var SqsClient
     */
    protected SqsClient $client;

    /**
     * Property name.
     *
     * @var string
     */
    protected string $channel;

    /**
     * SqsQueueDriver constructor.
     *
     * @param  string  $key
     * @param  string  $secret
     * @param  string  $channel
     * @param  array   $options
     */
    public function __construct(string $key, string $secret, string $channel = 'default', array $options = [])
    {
        $this->client = $this->getSqsClient($key, $secret, $options);

        $this->channel = $channel;
    }

    /**
     * push
     *
     * @param  QueueMessage  $message
     *
     * @return string
     * @throws JsonException
     */
    public function push(QueueMessage $message): string
    {
        $request = [
            'QueueUrl' => $this->getQueueUrl($message->getChannel()),
            'MessageBody' => json_encode($message, JSON_THROW_ON_ERROR),
        ];

        $request['DelaySeconds'] = $message->getDelay();

        $options = $message->getOptions();

        $request = array_merge($request, $options);

        return (string) $this->client->sendMessage($request)->get('MessageId');
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
        $result = $this->client->receiveMessage(
            [
                'QueueUrl' => $this->getQueueUrl($channel),
                'AttributeNames' => ['ApproximateReceiveCount'],
            ]
        );

        if ($result['Messages'] === null) {
            return null;
        }

        $data = $result['Messages'][0];

        $res = new QueueMessage();

        $res->setId($data['MessageId']);
        $res->setAttempts($data['Attributes']['ApproximateReceiveCount']);
        $res->setBody(json_decode($data['Body'], true));
        $res->setRawBody($data['Body']);
        $res->setChannel($channel ?: $this->channel);
        $res->set('ReceiptHandle', $data['ReceiptHandle']);

        return $res;
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
        $this->client->deleteMessage(
            [
                'QueueUrl' => $this->getQueueUrl($message->getChannel()),
                'ReceiptHandle' => $this->getReceiptHandle($message),
            ]
        );

        return $this;
    }

    /**
     * release
     *
     * @param  QueueMessage  $message
     *
     * @return static
     */
    public function release(QueueMessage $message): static
    {
        $this->client->changeMessageVisibility(
            [
                'QueueUrl' => $this->getQueueUrl($message->getChannel()),
                'ReceiptHandle' => $this->getReceiptHandle($message),
                'VisibilityTimeout' => $message->getDelay(),
            ]
        );

        return $this;
    }

    /**
     * getQueueUrl
     *
     * @param  string  $channel
     *
     * @return string
     */
    public function getQueueUrl(?string $channel = null): string
    {
        $channel = $channel ?: $this->channel;

        if (filter_var($channel, FILTER_VALIDATE_URL) !== false) {
            return $channel;
        }

        return $this->client->getQueueUrl(['QueueName' => $channel])->get('QueueUrl');
    }

    /**
     * getReceiptHandle
     *
     * @param  QueueMessage  $message
     *
     * @return  string
     */
    public function getReceiptHandle(QueueMessage $message): string
    {
        return $message->get('ReceiptHandle', $message->getId());
    }

    /**
     * getSqsClient
     *
     * @param  string  $key
     * @param  string  $secret
     * @param  array   $options
     *
     * @return  SqsClient
     * @throws DomainException
     */
    public function getSqsClient(string $key, string $secret, array $options = []): SqsClient
    {
        if (!class_exists(SqsClient::class)) {
            throw new DomainException('Please install aws/aws-sdk-php first.');
        }

        $defaultOptions = [
            'region' => 'ap-northeast-1',
            'version' => 'latest',
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
        ];

        $options = array_merge($defaultOptions, $options);

        return new SqsClient($options);
    }
}
