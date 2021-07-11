<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Options;

use DomainException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Windwalker\Utilities\Cache\RuntimeCacheTrait;

/**
 * The OptionsResolverFactory class.
 */
class OptionsResolverFactory
{
    use RuntimeCacheTrait;

    protected static array $instances = [];

    public static function getByClass(string $class): OptionsResolver
    {
        if (!class_exists(OptionsResolver::class)) {
            throw new DomainException('Please install symfony/options-resolver first');
        }

        return self::once('options:' . $class, fn() => new OptionsResolver());
    }

    public static function has(string $class): bool
    {
        return self::cacheHas('options:' . $class);
    }
}
