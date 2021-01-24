<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Windwalker\Pool\Exception\ConnectionPoolException;
use Windwalker\Pool\Stack\SingleStack;
use Windwalker\Pool\Stack\StackInterface;
use Windwalker\Pool\Stack\SwooleStack;
use Windwalker\Utilities\Options\OptionsResolverTrait;

use function Windwalker\swoole_in_coroutine;

/**
 * The AbstractPool class.
 */
abstract class AbstractPool implements PoolInterface, \Countable
{
    use OptionsResolverTrait;

    protected ?StackInterface $stack = null;

    protected bool $init = false;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * AbstractPool constructor.
     *
     * @param  int                   $maxSize
     * @param  array                 $options
     * @param  StackInterface|null   $stack
     * @param  LoggerInterface|null  $logger
     */
    public function __construct(
        int $maxSize = 1,
        array $options = [],
        ?StackInterface $stack = null,
        ?LoggerInterface $logger = null
    ) {
        $options['max_size'] = $maxSize;

        $this->resolveOptions($options, [$this, 'configureOptions']);

        $this->stack = $stack ?? $this->createStack();
        $this->logger = $logger ?? new NullLogger();
    }

    protected function createStack(): StackInterface
    {
        if (swoole_in_coroutine()) {
            return new SwooleStack($this->getOption('max_size'));
        }

        return new SingleStack();
    }

    /**
     * @param  LoggerInterface  $logger
     *
     * @return  static  Return self to support chaining.
     */
    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;

        return $this;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'max_size' => 1,
                'min_size' => 1,
                'max_active' => 1,
                'min_active' => 1,
                'max_wait' => 0,
                'max_wait_time' => 0,
                'max_idle_time' => 60,
                'max_close_time' => 3,
            ]
        )
            ->setAllowedTypes('max_size', 'int')
            ->setAllowedTypes('min_size', 'int')
            ->setAllowedTypes('max_active', 'int')
            ->setAllowedTypes('min_active', 'int')
            ->setAllowedTypes('max_wait', 'int')
            ->setAllowedTypes('max_wait_time', 'int')
            ->setAllowedTypes('max_idle_time', 'int')
            ->setAllowedTypes('max_close_time', 'int');
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        if ($this->init === true) {
            return;
        }

        for ($i = 0; $i < $this->getOption('min_active'); $i++) {
            $this->createConnection();
        }
    }

    /**
     * @inheritDoc
     */
    public function createConnection(): ConnectionInterface
    {
    }

    abstract public function create();

    /**
     * @inheritDoc
     */
    public function getConnection(): ConnectionInterface
    {
        // Less than min active
        if ($this->count() < $this->getOption('min_active')) {
            return $this->createConnection();
        }

        // Pop connections
        $connection = null;

        if ($this->stack->count() !== 0) {
            $connection = $this->popFromStack();
        }

        // Found a connection, return it.
        if ($connection !== null) {
            $connection->updateLastTime();
            return $connection;
        }

        // If no connections found, stack is empty
        // and if not reach max active number, create a new one.
        if ($this->count() < $this->getOption('max_active')) {
            return $this->createConnection();
        }

        $maxWait = $this->getOption('max_wait');
        if ($maxWait > 0 && $this->stack->waitingCount() >= $maxWait) {
            throw new ConnectionPoolException(
                sprintf(
                    'Waiting Consumer is full. max_wait=%d count=%d',
                    $maxWait,
                    $this->stack->count()
                )
            );
        }


    }

    /**
     * @inheritDoc
     */
    public function release(ConnectionInterface $connection): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getConnectionId(): int
    {
    }

    /**
     * @inheritDoc
     */
    public function remove(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function close(): int
    {
    }

    protected function popFromStack(): ?ConnectionInterface
    {
        $time = time();

        while ($this->stack->count() !== 0) {
            $connection = $this->stack->pop();

            $lastTime = $connection->getLastTime();

            // If out of max idle time, drop this connection.
            if (($time - $lastTime) > $this->getOption('max_idle_time')) {
                $connection->disconnect();
                continue;
            }

            return $connection;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->stack->count();
    }
}
