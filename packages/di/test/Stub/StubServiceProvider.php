<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;

/**
 * The StubServiceProvider class.
 *
 * @since  2.0
 */
class StubServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param  Container  $container  The DI container.
     *
     * @return  void
     */
    public function register(Container $container): void
    {
        $container->share(
            'bingo',
            function () {
                return 'Bingo';
            }
        );
    }
}
