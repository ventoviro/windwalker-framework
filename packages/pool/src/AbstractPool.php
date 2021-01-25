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
use Windwalker\Pool\Exception\WaitTimeoutException;
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

    public const MAX_SIZE = 'max_size';

    public const MIN_SIZE = 'min_size';

    public const MAX_ACTIVE = 'max_active';

    public const MIN_ACTIVE = 'min_active';

    public const MAX_WAIT = 'max_wait';

    public const WAIT_TIMEOUT = 'wait_timeout';

    public const IDLE_TIMEOUT = 'idle_timeout';

    public const CLOSE_TIMEOUT = 'close_timeout';

    protected ?StackInterface $stack = null;

    protected bool $init = false;

    protected int $serial = 0;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * AbstractPool constructor.
     *
     * @param  array                 $options
     * @param  StackInterface|null   $stack
     * @param  LoggerInterface|null  $logger
     */
    public function __construct(
        array $options = [],
        ?StackInterface $stack = null,
        ?LoggerInterface $logger = null
    ) {
        $this->resolveOptions($options, [$this, 'configureOptions']);

        $this->stack = $stack ?? $this->createStack();
        $this->logger = $logger ?? new NullLogger();
    }

    protected function createStack(): StackInterface
    {
        if (swoole_in_coroutine()) {
            return new SwooleStack($this->getOption(self::MAX_SIZE));
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
                self::MAX_SIZE => 1,
                self::MIN_SIZE => 1,
                self::MAX_ACTIVE => 1,
                self::MIN_ACTIVE => 1,
                self::MAX_WAIT => 0,
                self::WAIT_TIMEOUT => 0,
                self::IDLE_TIMEOUT => 60,
                self::CLOSE_TIMEOUT => 3,
            ]
        )
            ->setAllowedTypes(self::MAX_SIZE, 'int')
            ->setAllowedTypes(self::MIN_SIZE, 'int')
            ->setAllowedTypes(self::MAX_ACTIVE, 'int')
            ->setAllowedTypes(self::MIN_ACTIVE, 'int')
            ->setAllowedTypes(self::MAX_WAIT, 'int')
            ->setAllowedTypes(self::WAIT_TIMEOUT, 'int')
            ->setAllowedTypes(self::IDLE_TIMEOUT, 'int')
            ->setAllowedTypes(self::CLOSE_TIMEOUT, 'int');
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        if ($this->init === true) {
            return;
        }

        if (swoole_in_coroutine()) {
            for ($i = 0; $i < $this->getOption(self::MIN_ACTIVE); $i++) {
                $this->createConnection();
            }
        } else {
            $this->createConnection();
        }

        $this->init = true;
    }

    /**
     * @inheritDoc
     */
    public function createConnection(): ConnectionInterface
    {
        $connection = $this->create();
        $connection->setPool($this);
        $connection->release(true);

        return $connection;
    }

    abstract public function create(): ConnectionInterface;

    /**
     * @inheritDoc
     */
    public function getConnection(): ConnectionInterface
    {
        // Less than min active
        if ($this->count() < $this->getOption(self::MIN_ACTIVE)) {
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
        if ($this->count() < $this->getOption(self::MAX_ACTIVE)) {
            return $this->createConnection();
        }

        $maxWait = $this->getOption(self::MAX_WAIT);
        if ($maxWait > 0 && $this->stack->waitingCount() >= $maxWait) {
            throw new ConnectionPoolException(
                sprintf(
                    'Waiting Consumer is full. max_wait=%d count=%d',
                    $maxWait,
                    $this->stack->count()
                )
            );
        }

        $connection = $this->stack->pop($this->getOption(self::WAIT_TIMEOUT));

        $connection->updateLastTime();

        return $connection;
    }

    /**
     * @inheritDoc
     */
    public function release(ConnectionInterface $connection): void
    {
        if ($this->stack->count() < $this->getOption(self::MAX_ACTIVE)) {
            $connection->setActive(false);
            $this->stack->push($connection);
            return;
        }

        // Disconnect then drop it.
        $connection->disconnect();
    }

    /**
     * @inheritDoc
     */
    public function getSerial(): int
    {
        return ++$this->serial;
    }

    /**
     * @inheritDoc
     */
    public function close(): int
    {
        if ($this->stack === null) {
            return 0;
        }

        $length = $closed = $this->stack->count();

        while ($length) {
            $connection = $this->stack->pop($this->getOption(self::CLOSE_TIMEOUT));

            try {
                $connection->disconnect();
            } catch (\Throwable $e) {
                $this->logger->warning(
                    sprintf(
                        'Error while closing connection: %s',
                        $e->getMessage()
                    )
                );
            }

            $length--;
        }

        return $closed;
    }

    protected function popFromStack(): ?ConnectionInterface
    {
        $time = time();

        while ($this->stack->count() !== 0) {
            $connection = $this->stack->pop();

            $lastTime = $connection->getLastTime();

            // If out of max idle time, drop this connection.
            if (($time - $lastTime) > $this->getOption(self::IDLE_TIMEOUT)) {
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
