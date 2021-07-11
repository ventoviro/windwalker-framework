<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue;

use DateTimeImmutable;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Event\EventListenableInterface;
use Windwalker\Queue\Event\AfterJobRunEvent;
use Windwalker\Queue\Event\BeforeJobRunEvent;
use Windwalker\Queue\Event\JobFailureEvent;
use Windwalker\Queue\Event\LoopEndEvent;
use Windwalker\Queue\Event\LoopFailureEvent;
use Windwalker\Queue\Event\LoopStartEvent;
use Windwalker\Queue\Event\StopEvent;
use Windwalker\Queue\Exception\MaxAttemptsExceededException;
use Windwalker\Queue\Job\JobInterface;
use Windwalker\Queue\Job\NullJob;

/**
 * The Worker class.
 *
 * @since  3.2
 */
class Worker implements EventListenableInterface
{
    use EventAwareTrait;

    public const STATE_INACTIVE = 'inactive';

    public const STATE_ACTIVE = 'active';

    public const STATE_EXITING = 'exiting';

    public const STATE_PAUSE = 'pause';

    public const STATE_STOP = 'stop';

    /**
     * Property queue.
     *
     * @var  Queue
     */
    protected Queue $queue;

    /**
     * Property logger.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Property state.
     *
     * @var  string
     */
    protected string $state = self::STATE_INACTIVE;

    /**
     * Property lastRestart.
     *
     * @var  int
     */
    protected ?int $lastRestart = null;

    /**
     * Property pid.
     *
     * @var  int
     */
    protected ?int $pid = null;

    /**
     * Worker constructor.
     *
     * @param  Queue            $queue
     * @param  LoggerInterface  $logger
     */
    public function __construct(Queue $queue, ?LoggerInterface $logger = null)
    {
        $this->queue = $queue;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * loop
     *
     * @param  string|array  $channel
     * @param  array         $options
     *
     * @return  void
     * @throws Exception
     */
    public function loop(string|array $channel, array $options = [])
    {
        gc_enable();

        // Last Restart
        $this->lastRestart = (int) (new DateTimeImmutable('now'))->format('U');

        // Log PID
        $this->pid = getmypid();

        $this->logger->info('A worker start running... PID: ' . $this->pid);

        $this->setState(static::STATE_ACTIVE);

        while (!$this->isStop()) {
            $this->gc();

            $worker = $this;
            $queue = $this->queue;

            // @loop start
            $this->emit(LoopStartEvent::class, compact('worker', 'queue'));

            // Timeout handler
            $this->registerSignals($options);

            if (($options['force'] ?? null) || $this->canLoop()) {
                try {
                    $this->runNextJob($channel, $options);
                } catch (Exception $exception) {
                    $message = sprintf('Worker failure in a loop cycle: %s', $exception->getMessage());

                    $this->logger->error($message);

                    $this->emit(LoopFailureEvent::class, compact('worker', 'exception', 'message'));
                }
            }

            $this->stopIfNecessary($options);

            // @loop end
            $this->emit(LoopEndEvent::class, compact('worker', 'queue'));

            $this->sleep((float) ($options['sleep'] ?? 1));
        }
    }

    /**
     * runNextJob
     *
     * @param  string|array  $channel
     * @param  array         $options
     *
     * @return  void
     */
    public function runNextJob(string|array $channel, array $options): void
    {
        $message = $this->getNextMessage($channel);

        if (!$message) {
            return;
        }

        $this->process($message, $options);
    }

    /**
     * process
     *
     * @param  QueueMessage  $message
     * @param  array         $options
     *
     * @return  void
     */
    public function process(QueueMessage $message, array $options)
    {
        $maxTries = (int) ($options['tries'] ?? 5);

        $job = $message->getSerializedJob();
        /** @var JobInterface $job */
        $job = unserialize($job);

        try {
            // @before event
            $this->emit(
                BeforeJobRunEvent::class,
                [
                    'worker' => $this,
                    'message' => $message,
                    'job' => $job,
                    'queue' => $this->queue,
                ]
            );

            // Fail if max attempts
            if ($maxTries !== 0 && $maxTries < $message->getAttempts()) {
                $this->queue->delete($message);

                throw new MaxAttemptsExceededException('Max attempts exceed for Message: ' . $message->getId());
            }

            // run
            $this->runJob($job);

            // @after event
            $this->emit(
                AfterJobRunEvent::class,
                [
                    'worker' => $this,
                    'message' => $message,
                    'job' => $job,
                    'queue' => $this->queue,
                ]
            );

            $this->queue->delete($message);
        } catch (Throwable $t) {
            $this->handleJobException($job, $message, $options, $t);
        } finally {
            if (!$message->isDeleted()) {
                $this->queue->release($message, (int) ($options['delay'] ?? 0));
            }
        }
    }

    /**
     * runJob
     *
     * @param  JobInterface  $job
     *
     * @return  mixed
     */
    protected function runJob(JobInterface $job): mixed
    {
        return $job();
    }

    /**
     * canLoop
     *
     * @return  bool
     */
    protected function canLoop(): bool
    {
        return $this->getState() === static::STATE_ACTIVE;
    }

    /**
     * registerTimeoutHandler
     *
     * @param  array  $options
     *
     * @return  void
     */
    protected function registerSignals(array $options): void
    {
        $timeout = (int) ($options['timeout'] ?? 60);

        if (!extension_loaded('pcntl')) {
            return;
        }

        declare(ticks=1);

        if ($timeout !== 0) {
            pcntl_signal(
                SIGALRM,
                function () use ($timeout) {
                    $this->stop('A job process over the max timeout: ' . $timeout . ' PID: ' . $this->pid);
                }
            );

            pcntl_alarm((int) ($timeout + $options['sleep']));
        }

        // Wait job complete then stop
        pcntl_signal(SIGINT, [$this, 'shutdown']);
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
    }

    /**
     * shoutdown
     *
     * @return  void
     */
    public function shutdown(): void
    {
        $this->setState(static::STATE_EXITING);
    }

    /**
     * stop
     *
     * @param  string  $reason
     * @param  int     $code
     * @param  bool    $instant
     *
     * @return void
     */
    public function stop(string $reason = 'Unknown reason', int $code = 0, bool $instant = false): void
    {
        $this->logger->info('Worker stop: ' . $reason);

        $this->emit(
            StopEvent::class,
            [
                'worker' => $this,
                'queue' => $this->queue,
                'reason' => $reason,
            ]
        );

        $this->setState(static::STATE_STOP);

        if ($instant) {
            exit($code);
        }
    }

    public function isStop(): bool
    {
        return in_array(
            $this->getState(),
            [
                static::STATE_EXITING,
                static::STATE_STOP,
            ],
            true
        );
    }

    /**
     * handleException
     *
     * @param  JobInterface  $job
     * @param  QueueMessage  $message
     * @param  array         $options
     * @param  Throwable    $e
     *
     * @return void
     */
    protected function handleJobException(mixed $job, QueueMessage $message, array $options, Throwable $e): void
    {
        if (!$job instanceof JobInterface) {
            $job = new NullJob();
        }

        $this->logger->error(
            sprintf(
                'Job [%s] (%s) failed: %s - Class: %s',
                $job->getName(),
                $message->getId(),
                $e->getMessage(),
                get_class($job)
            )
        );

        if (method_exists($job, 'failed')) {
            $job->failed($e);
        }

        $maxTries = (int) ($options['tries'] ?? 5);

        // Delete and log error if reach max attempts.
        if ($maxTries !== 0 && $maxTries <= $message->getAttempts()) {
            $this->queue->delete($message);
            $this->logger->error(
                sprintf(
                    'Max attempts exceeded. Job: %s (%s) - Class: %s',
                    $job->getName(),
                    $message->getId(),
                    get_class($job)
                )
            );
        }

        $this->dispatcher->emit(
            JobFailureEvent::class,
            [
                'worker' => $this,
                'queue' => $this->queue,
                'exception' => $e,
                'job' => $job,
                'message' => $message,
            ]
        );
    }

    /**
     * getNextMessage
     *
     * @param  string|array  $channel
     *
     * @return  null|QueueMessage
     */
    protected function getNextMessage(string|array $channel): ?QueueMessage
    {
        $channel = (array) $channel;

        foreach ($channel as $channelName) {
            if ($message = $this->queue->pop($channelName)) {
                return $message;
            }
        }

        return null;
    }

    /**
     * sleep
     *
     * @param  float  $seconds
     *
     * @return  void
     */
    protected function sleep(float $seconds): void
    {
        usleep((int) ($seconds * 1000000));
    }

    /**
     * Method to perform basic garbage collection and memory management in the sense of clearing the
     * stat cache.  We will probably call this method pretty regularly in our main loop.
     *
     * @return  void
     */
    protected function gc(): void
    {
        // Perform generic garbage collection.
        gc_collect_cycles();

        // Clear the stat cache so it doesn't blow up memory.
        clearstatcache();
    }

    /**
     * Method to get property Manager
     *
     * @return  Queue
     */
    public function getQueue(): Queue
    {
        return $this->queue;
    }

    /**
     * Method to get property State
     *
     * @return  string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * setState
     *
     * @param  string  $state
     *
     * @return  void
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * stopIfNecessary
     *
     * @param  array  $options
     *
     * @return  void
     */
    public function stopIfNecessary(array $options): void
    {
        $restartSignal = $options['restart_signal'] ?? null;

        if ($restartSignal && is_file($restartSignal)) {
            $signal = file_get_contents($restartSignal);

            if ($this->lastRestart < $signal) {
                $this->stop('Receive restart signal. PID: ' . $this->pid);
            }
        }

        if ((memory_get_usage() / 1024 / 1024) >= (int) ($options['memory_limit'] ?? 128)) {
            $this->stop('Memory usage exceeded. PID: ' . $this->pid);
        }

        if ($this->getState() === static::STATE_EXITING) {
            $this->stop('Shutdown by signal. PID: ' . $this->pid);
        }
    }
}
