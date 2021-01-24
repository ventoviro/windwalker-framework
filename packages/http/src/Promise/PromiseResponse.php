<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Promise;

use Windwalker\Http\Response\Response;

/**
 * The PromiseResponse class.
 *
 * @since  3.4
 */
class PromiseResponse extends Response
{
    /**
     * Property thenCallables.
     *
     * @var  callable[]
     */
    protected $thenCallables = [];

    /**
     * Property rejectCallables.
     *
     * @var  callable[]
     */
    protected $rejectCallables = [];

    /**
     * then
     *
     * @param callable $callback
     *
     * @return  static
     *
     * @since  3.4
     */
    public function then(callable $callback): static
    {
        $this->thenCallables[] = $callback;

        return $this;
    }

    /**
     * reject
     *
     * @param callable $callback
     *
     * @return  static
     *
     * @since  3.4
     */
    public function fail(callable $callback): static
    {
        $this->rejectCallables[] = $callback;

        return $this;
    }

    /**
     * resolve
     *
     * @param mixed $value
     *
     * @return  mixed
     *
     * @since  3.4
     */
    public function resolve(mixed $value): mixed
    {
        try {
            foreach ($this->thenCallables as $then) {
                $value = $then($value);
            }
        } catch (\Exception $e) {
            foreach ($this->rejectCallables as $reject) {
                $e = $reject($e);
            }
        }

        return $value;
    }

    /**
     * reject
     *
     * @param \Exception|\Throwable $e
     *
     * @return  mixed
     *
     * @since  3.4.5
     */
    public function reject(mixed $e): mixed
    {
        foreach ($this->rejectCallables as $reject) {
            $e = $reject($e);
        }

        return $e;
    }
}
