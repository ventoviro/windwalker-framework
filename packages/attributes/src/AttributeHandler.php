<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Attributes;

/**
 * The AttributeHandler class.
 */
class AttributeHandler
{
    /**
     * @var callable
     */
    public $handler;

    /**
     * AttributeHandler constructor.
     *
     * @param  callable            $handler
     * @param  \Reflector          $reflector
     * @param  object|null         $object
     * @param  AttributesResolver  $resolver
     */
    public function __construct(
        callable $handler,
        protected \Reflector $reflector,
        protected ?object $object,
        protected AttributesResolver $resolver
    ) {
        $this->set($handler);
    }

    public function __invoke(&...$args): mixed
    {
        // try {
            return ($this->handler)(...$args);
        // } catch (\Throwable $e) {
        //     show($this->handler, $this);
        //     throw $e;
        // }
    }

    public function set(callable $handler): static
    {
        $this->handler = $handler;

        return $this;
    }

    public function get(): callable
    {
        return $this->handler;
    }

    /**
     * @return \Reflector
     */
    public function getReflector(): \Reflector
    {
        return $this->reflector;
    }

    /**
     * @return AttributesResolver
     */
    public function getResolver(): AttributesResolver
    {
        return $this->resolver;
    }

    /**
     * @return mixed
     */
    public function getObject(): mixed
    {
        return $this->object;
    }
}
