<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\DI\Concern;

use Closure;
use Windwalker\DI\BootableDeferredProviderInterface;
use Windwalker\DI\BootableProviderInterface;
use Windwalker\DI\Exception\DefinitionException;
use Windwalker\DI\ServiceProviderInterface;
use Windwalker\Utilities\Assert\Assert;
use Windwalker\Utilities\Assert\TypeAssert;

use function Windwalker\DI\share;

/**
 * Trait ConfigLoaderTrait
 */
trait ConfigRegisterTrait
{
    public function registerByConfig(array|string $config): void
    {
        if (is_string($config)) {
            $config = include $config;

            TypeAssert::assert(
                is_array($config),
                'Config should be array, {value} given.',
                $config
            );
        }

        $this->registerAttributes($config['attributes'] ?? []);
        $this->registerBindings($config['bindings'] ?? []);
        $this->registerProviders($config['providers'] ?? []);
        $this->registerAlias($config['aliases'] ?? []);
        $this->registerExtends($config['extends'] ?? []);
    }

    protected function registerAttributes(array $config): void
    {
        $resolver = $this->getAttributesResolver();

        foreach ($config as $key => $value) {
            if ($value instanceof Closure) {
                $value($this);
            } elseif (is_numeric($key)) {
                $resolver->registerAttribute($value);
            } else {
                $resolver->registerAttribute($key, $value);
            }
        }
    }

    protected function registerBindings(array $config): void
    {
        foreach ($config as $key => $value) {
            if (is_numeric($key)) {
                if (!is_string($value)) {
                    throw new DefinitionException(
                        sprintf(
                            'Binding classes must with a string key, %s given.',
                            Assert::describeValue($value)
                        )
                    );
                }

                $key = $value;
            }

            if (is_string($value)) {
                $value = share($value);
            }

            $this->set($key, $value);
        }
    }

    protected function registerProviders(array $config): void
    {
        $bootDeferred = [];

        foreach ($config as $provider) {
            $provider = $this->resolve($provider);

            if ($provider instanceof ServiceProviderInterface) {
                $this->registerServiceProvider($provider);
            }

            if ($provider instanceof BootableProviderInterface) {
                $provider->boot($this);
            }

            if ($provider instanceof BootableDeferredProviderInterface) {
                $bootDeferred[] = $provider;
            }
        }

        foreach ($bootDeferred as $provider) {
            $provider->bootDeferred($this);
        }
    }

    protected function registerAlias(array $config): void
    {
        foreach ($config as $alias => $id) {
            $this->alias($alias, $id);
        }
    }

    protected function registerExtends(array $config): void
    {
        foreach ($config as $class => $extend) {
            $this->extend($class, $extend);
        }
    }
}
