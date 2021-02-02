<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Database;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Windwalker\Utilities\Options\OptionsResolverTrait;

/**
 * Trait DatabaseOptionsTrait
 */
trait DatabaseOptionsTrait
{
    use OptionsResolverTrait;

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'driver' => null,
                'host' => 'localhost',
                'database' => null,
                'username' => null,
                'password' => null,
                'port' => null,
                'prefix' => null,
                'charset' => null,
                'driverOptions' => [],
            ]
        )
            ->setRequired(
                [
                    'driver',
                    'host',
                    'username'
                ]
            )
            ->setAllowedTypes('driver', 'string');
    }
}
