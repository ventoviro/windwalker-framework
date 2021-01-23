<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Environment;

/**
 * The Environment class.
 *
 * @since  2.0
 */
class Environment
{
    /**
     * Property server.
     *
     * @var  Platform
     */
    public Platform $platform;

    /**
     * Class init.
     *
     * @param Platform $platform
     */
    public function __construct(?Platform $platform = null)
    {
        $this->platform = $platform ?? new Platform();
    }

    /**
     * Method to get property Server
     *
     * @return  Platform
     */
    public function getPlatform(): Platform
    {
        return $this->platform;
    }

    /**
     * Method to set property server
     *
     * @param   Platform $platform
     *
     * @return  static  Return self to support chaining.
     */
    public function setPlatform(Platform $platform): static
    {
        $this->platform = $platform;

        return $this;
    }
}
