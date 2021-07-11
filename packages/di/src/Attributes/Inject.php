<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use Attribute;
use JetBrains\PhpStorm\Pure;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;
use RuntimeException;
use Windwalker\DI\Container;
use Windwalker\DI\Exception\DependencyResolutionException;

/**
 * The Inject class.
 *
 * @since  3.4.4
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Inject implements ContainerAttributeInterface
{
    public ?string $id = null;

    public bool $forceNew = false;

    /**
     * Inject constructor.
     *
     * @param  string|null  $id
     * @param  bool         $forceNew
     */
    public function __construct(?string $id = null, bool $forceNew = false)
    {
        $this->id = $id;
        $this->forceNew = $forceNew;
    }

    /**
     * __invoke
     *
     * @param  AttributeHandler  $handler
     *
     * @return mixed
     */
    #[Pure]
    public function __invoke(
        AttributeHandler $handler
    ): callable {
        $reflector = $handler->getReflector();

        return function (...$args) use ($handler, $reflector) {
            if ($reflector instanceof ReflectionParameter) {
                return $this->handleParameter($handler);
            }

            if ($handler->getObject() === null) {
                throw new RuntimeException('No object to inject.');
            }

            $varClass = $this->getTypeName($reflector);

            $value = $this->resolveInjectable($handler->getContainer(), $varClass);

            $reflector->setValue($handler->getObject(), $value);

            return $value;
        };
    }

    protected function handleParameter(AttributeHandler $handler): mixed
    {
        $varClass = $this->getTypeName($handler->getReflector());

        return $this->resolveInjectable($handler->getContainer(), $varClass);
    }

    protected function getTypeName(ReflectionProperty|ReflectionParameter $reflector): mixed
    {
        $type = $reflector->getType();

        if ($this->id) {
            $varClass = $this->id;
        } else {
            if ($type instanceof ReflectionUnionType) {
                $types = [$type->getTypes()];
            } else {
                $types = [$type];
            }

            $varClass = null;

            foreach ($types as $type) {
                if (class_exists($type->getName()) || interface_exists($type->getName())) {
                    $varClass = $type->getName();
                    break;
                }
            }
        }

        if (!$varClass) {
            throw new DependencyResolutionException(
                sprintf('unable to resolve injection of property: "%s".', $reflector->getName())
            );
        }

        return $varClass;
    }

    /**
     * getInjectable
     *
     * @param  Container  $container
     * @param  string     $class
     *
     * @return  mixed
     *
     * @throws DependencyResolutionException
     */
    public function resolveInjectable(Container $container, string $class): mixed
    {
        $id = $class;

        if ($container->has($id)) {
            return $container->get($id, $this->forceNew);
        }

        if (!class_exists($id)) {
            throw new DependencyResolutionException(
                sprintf('Class: "%s" not exists.', $id)
            );
        }

        return $this->createObject($container, $id);
    }

    protected function createObject(Container $container, string $id): object
    {
        return $container->newInstance($id);
    }
}
