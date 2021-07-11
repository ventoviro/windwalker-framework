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
use Throwable;
use Windwalker\Pool\Exception\ConnectionPoolException;
use Windwalker\Pool\Stack\SingleStack;
use Windwalker\Pool\Stack\StackInterface;
use Windwalker\Pool\Stack\SwooleStack;
use Windwalker\Utilities\Options\OptionsResolverTrait;

use function Windwalker\swoole_in_coroutine;

/**
 * The AbstractPool class.
 */
abstract class AbstractPool implements PoolInterface
{
    use OptionsResolverTrait;

    public const MAX_SIZE = 'max_size';

    public const MIN_SIZE = 'min_size';

    public const MAX_WAIT = 'max_wait';

    public const WAIT_TIMEOUT = 'wait_timeout';

    public const IDLE_TIMEOUT = 'idle_timeout';

    public const CLOSE_TIMEOUT = 'close_timeout';

    /**
     * @var StackInterface|null
     */
    protected ?StackInterface $stack = null;

    /**
     * @var bool
     */
    protected bool $init = false;

    /**
     * @var int
     */
    protected int $serial = 0;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var int
     */
    protected int $totalCount = 0;

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
                self::MAX_WAIT => -1,
                self::WAIT_TIMEOUT => -1,
                self::IDLE_TIMEOUT => 60,
                self::CLOSE_TIMEOUT => 3,
            ]
        )
            ->setAllowedTypes(self::MAX_SIZE, 'int')
            ->setAllowedTypes(self::MIN_SIZE, 'int')
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

        if (!$this->stack instanceof SingleStack) {
            for ($i = 0; $i < $this->getOption(self::MIN_SIZE); $i++) {
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
        $this->totalCount++;

        $connection = $this->create();
        $connection->setPool($this);
        $connection->updateLastTime();
        $connection->release(true);

        return $connection;
    }

    /**
     * Create a new connection.
     *
     * @return  ConnectionInterface
     */
    abstract protected function create(): ConnectionInterface;

    /**
     * @inheritDoc
     */
    public function dropConnection(ConnectionInterface $connection): void
    {
        $this->totalCount--;
        $connection->disconnect();
        $connection->setPool(null);
    }

    /**
     * pop
     *
     * @param  int|null  $timeout
     *
     * @return  ConnectionInterface
     */
    protected function pop(?int $timeout = null): ConnectionInterface
    {
        return $this->popPreprocess($this->stack->pop($timeout));
    }

    protected function popPreprocess(ConnectionInterface $connection): ConnectionInterface
    {
        $connection->updateLastTime();
        $connection->setActive(true);

        return $connection;
    }

    protected function createAndPop(): ConnectionInterface
    {
        $this->createConnection();

        return $this->pop();
    }

    /**
     * @inheritDoc
     */
    public function getConnection(): ConnectionInterface
    {
        // Less than min active
        if ($this->totalCount() < $this->getOption(self::MIN_SIZE)) {
            return $this->createAndPop();
        }

        // Pop connections
        $connection = null;

        if ($this->count() !== 0 && $connection = $this->clearExpiredAndPop()) {
            // Found a connection, return it.
            return $connection;
        }

        // If no connections found, stack is empty
        // and if not reach max active number, create a new one.
        if ($this->totalCount() < $this->getOption(self::MAX_SIZE)) {
            return $this->createAndPop();
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

        return $this->pop($this->getOption(self::WAIT_TIMEOUT, -1));
    }

    /**
     * @inheritDoc
     */
    public function release(ConnectionInterface $connection): void
    {
        if ($this->stack->count() < $this->getOption(self::MAX_SIZE)) {
            $connection->setActive(false);
            $this->stack->push($connection);

            return;
        }

        // Disconnect then drop it.
        $connection->disconnect();
        $this->totalCount--;
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

        $length = $closed = $this->count();

        while ($length) {
            $connection = $this->stack->pop($this->getOption(self::CLOSE_TIMEOUT));

            try {
                $this->dropConnection($connection);
            } catch (Throwable $e) {
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

    protected function clearExpiredAndPop(): ?ConnectionInterface
    {
        $time = time();

        while ($this->stack->count() !== 0) {
            $connection = $this->stack->pop();

            $lastTime = $connection->getLastTime();

            // If out of max idle time, drop this connection.
            if (($time - $lastTime) > $this->getOption(self::IDLE_TIMEOUT)) {
                $connection->disconnect();
                $this->totalCount--;
                continue;
            }

            return $this->popPreprocess($connection);
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

    /**
     * totalCount
     *
     * @return  int
     */
    public function totalCount(): int
    {
        return $this->totalCount;
    }

    public function __destruct()
    {
        $this->close();
    }
}
