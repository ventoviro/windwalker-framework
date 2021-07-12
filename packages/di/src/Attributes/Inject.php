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
        /** @var ReflectionProperty|ReflectionParameter $reflector */
        $reflector = $handler->getReflector();

        return function (...$args) use ($handler, $reflector) {
            if ($reflector instanceof ReflectionParameter) {
                return $this->handleParameter($handler);
            }

            if ($handler->getObject() === null) {
                throw new RuntimeException('No object to inject.');
            }

            $value = $this->resolveInjectable($handler->getContainer(), $reflector);

            $reflector->setValue($handler->getObject(), $value);

            return $value;
        };
    }

    protected function handleParameter(AttributeHandler $handler): mixed
    {
        return $this->resolveInjectable($handler->getContainer(), $handler->getReflector());
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
                sprintf('Unable to resolve injection of property: "%s".', $reflector->getName())
            );
        }

        return $varClass;
    }

    public function resolveInjectable(Container $container, ReflectionProperty|ReflectionParameter $reflector): mixed
    {
        $id = $this->getTypeName($reflector);

        if ($container->has($id)) {
            return $container->get($id, $this->forceNew);
        }

        if (class_exists($id) || interface_exists($id)) {
            return $this->createObject($container, $id);
        }

        if (!$reflector->allowsNull()) {
            $class = $reflector->getDeclaringClass();
            $member = $reflector->getName();

            throw new DependencyResolutionException(
                "Unable to inject object $id for class $class::$member"
            );
        }

        return null;
    }

    protected function createObject(Container $container, string $id): object
    {
        return $container->newInstance($id);
    }
}
