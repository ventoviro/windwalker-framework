<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use ReflectionObject;
use Reflector;
use Windwalker\Attributes\AttributesResolver as GlobalAttributesResolver;
use Windwalker\DI\Container;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The AttributesResolver class.
 */
class AttributesResolver extends GlobalAttributesResolver
{
    protected Container $container;

    /**
     * AttributesResolver constructor.
     *
     * @param  Container  $container
     * @param  array      $options
     */
    public function __construct(Container $container, array $options = [])
    {
        $this->container = $container;

        parent::__construct($options);
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @param  Container  $container
     *
     * @return  static  Return self to support chaining.
     */
    public function setContainer(Container $container): static
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Resolve class constructor and return create function.
     *
     * @param  string         $class
     * @param  callable|null  $builder
     *
     * @return  AttributeHandler
     *
     * @throws \ReflectionException
     */
    public function resolveClassCreate(string $class, ?callable $builder = null): AttributeHandler
    {
        $ref = new \ReflectionClass($class);

        $builder = $builder ?? fn($args, int $options) => $this->getBuilder()($class, ...$args);

        $handler = $this->createHandler($builder, $ref);

        foreach ($ref->getAttributes() as $attribute) {
            if ($this->hasAttribute($attribute, \Attribute::TARGET_CLASS)) {
                $handler = $this->runAttribute($attribute, $handler);
            }
        }

        return $handler;
    }

    protected function prepareAttribute(object $attribute): void
    {
        // If Attribute need inject, we inject services here.
        $ref = new ReflectionObject($attribute);

        foreach ($ref->getProperties() as $property) {
            $attrs = $property->getAttributes(Inject::class);

            foreach ($attrs as $attr) {
                ReflectAccessor::setValue($attribute, $property->getName(), $attr);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function createHandler(callable $getter, Reflector $reflector, ?object $object = null): AttributeHandler
    {
        return new AttributeHandler($getter, $reflector, $object, $this, $this->container);
    }
}
