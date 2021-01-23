<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Server;

/**
 * The AdaptableServer class.
 */
abstract class AdaptableServer extends AbstractServer
{
    protected ServerInterface $adapter;

    /**
     * AdaptableServer constructor.
     *
     * @param  ServerInterface|null  $adapter
     */
    public function __construct(?ServerInterface $adapter = null)
    {
        $this->adapter = $adapter ?? new PhpServer();
    }

    /**
     * @return ServerInterface
     */
    public function getAdapter(): ServerInterface
    {
        return $this->adapter;
    }

    /**
     * @param  ServerInterface  $adapter
     *
     * @return  static  Return self to support chaining.
     */
    public function setAdapter(ServerInterface $adapter): static
    {
        $this->adapter = $adapter;

        return $this;
    }
}
