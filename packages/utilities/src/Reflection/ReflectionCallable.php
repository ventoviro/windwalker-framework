<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Reflection;

use Closure;
use Windwalker\Utilities\Assert\Assert;

/**
 * The ReflectionClosure class.
 */
class ReflectionCallable implements \Reflector
{
    public const TYPE_FUNCTION = 1;
    public const TYPE_OBJECT_METHOD = 2;
    public const TYPE_STATIC_METHOD = 3;
    public const TYPE_CLOSURE = 4;

    protected int $type = 0;

    /**
     * @var callable
     */
    protected $callable;

    protected ?string $class = null;

    protected ?object $instance = null;

    protected ?string $function = null;

    /**
     * ReflectionCallable constructor.
     *
     * @param  callable  $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;

        $this->extractCallable();
    }

    public function getClosure(): Closure
    {
        if ($this->type === static::TYPE_CLOSURE) {
            return $this->callable;
        }

        if ($this->type === static::TYPE_STATIC_METHOD || $this->type === static::TYPE_OBJECT_METHOD) {
            $target = $this->instance ?? $this->class;

            // If method is not exists, could be a magic method, wrap it with closure.
            if (!method_exists($target, $this->function)) {
                $callable = [$target, $this->function];
                return function (...$args) use ($callable) {
                    return $callable(...$args);
                };
            }
        }

        return Closure::fromCallable($this->callable);
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getReflector(): \ReflectionFunctionAbstract
    {
        if ($this->type === static::TYPE_CLOSURE || $this->type === static::TYPE_FUNCTION) {
            return new \ReflectionFunction($this->callable);
        }

        $target = $this->instance ?? $this->class;

        // If method is not exists, could be a magic method, wrap it with closure.
        if (!method_exists($target, $this->function)) {
            $callable = [$target, $this->function];
            return new \ReflectionFunction(fn(...$args) => $callable(...$args));
        }

        return new \ReflectionMethod($target, $this->function);
    }

    protected function extractCallable(): void
    {
        $callable = $this->callable;

        if (is_string($callable)) {
            if (str_contains($callable, '::')) {
                [$this->class, $this->function] = explode('::', $callable, 2);

                $this->type = static::TYPE_STATIC_METHOD;
            } else {
                $this->function = $callable;

                $this->type = static::TYPE_FUNCTION;
            }

            return;
        }

        if (is_array($callable)) {
            [$class, $this->function] = $callable;

            if (is_string($class)) {
                $this->class = $class;
                $this->type = static::TYPE_STATIC_METHOD;
            } elseif (is_object($class)) {
                $this->instance = $class;
                $this->type = static::TYPE_OBJECT_METHOD;
            }

            return;
        }

        if ($callable instanceof Closure) {
            $this->instance = $callable;
            $this->type = static::TYPE_CLOSURE;
            return;
        }

        if (is_object($callable)) {
            $this->instance = $callable;
            $this->function = '__invoke';
            $this->type = static::TYPE_OBJECT_METHOD;
            return;
        }

        throw new \LogicException('Unknown callable: ' . Assert::describeValue($callable));
    }

    /**
     * Exports a class
     *
     * @link       https://php.net/manual/en/reflector.export.php
     * @return string|null
     * @deprecated 7.4
     * @removed    8.0
     */
    public static function export(): ?string
    {
        return null;
    }

    /**
     * Returns the string representation of any Reflection object.
     *
     * Please note that since PHP 8.0 this method is absent in this interface
     * and inherits from the {@see Stringable} parent.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getReflector();
    }

    /**
     * @return string|null
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @return object|null
     */
    public function getObject(): ?object
    {
        return $this->instance;
    }

    /**
     * @return string|null
     */
    public function getFunctionName(): ?string
    {
        return $this->function;
    }

    public function getObjectOrClass(): object|string|null
    {
        return $this->instance ?? $this->class;
    }
}
