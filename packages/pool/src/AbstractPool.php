<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Windwalker\Pool\Driver\DriverInterface;
use Windwalker\Pool\Driver\SingleDriver;
use Windwalker\Pool\Driver\SwooleDriver;
use Windwalker\Utilities\Options\OptionsResolverTrait;

use function Windwalker\swoole_in_coroutine;

/**
 * The AbstractPool class.
 */
class AbstractPool implements PoolInterface
{
    use OptionsResolverTrait;

    protected ?DriverInterface $driver = null;

    protected bool $init = false;

    /**
     * AbstractPool constructor.
     *
     * @param  int                   $maxSize
     * @param  array                 $options
     * @param  DriverInterface|null  $driver
     */
    public function __construct(int $maxSize = 1, array $options = [], ?DriverInterface $driver = null)
    {
        $options['max_size'] = $maxSize;

        $this->resolveOptions($options, [$this, 'configureOptions']);

        $this->driver = $driver ?? $this->createDriver();
    }

    protected function createDriver(): DriverInterface
    {
        if (swoole_in_coroutine()) {
            return new SwooleDriver($this->getOption('max_size'));
        }

        return new SingleDriver();
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'max_size' => 1,
                'min_size' => 1,
                'min_active' => 1,
                'max_wait' => 0,
                'max_wait_time' => 0,
                'max_idle_time' => 60,
                'max_close_time' => 3,
            ]
        )
            ->setAllowedTypes('max_size', 'int')
            ->setAllowedTypes('min_size', 'int')
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

        }
    }

    /**
     * @inheritDoc
     */
    public function createConnection(): ConnectionInterface
    {
    }

    /**
     * @inheritDoc
     */
    public function getConnection(): ConnectionInterface
    {
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
}
