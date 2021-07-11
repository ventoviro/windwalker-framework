<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Attributes\Test\Stub\Attrs;

use Attribute;
use ReflectionProperty;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

/**
 * The PropWrap class.
 */
#[Attribute]
class PropWrap implements AttributeInterface
{
    public $value;

    public string $wrap;

    /**
     * PropWrap constructor.
     *
     * @param $wrap
     */
    public function __construct(string $wrap)
    {
        $this->wrap = $wrap;
    }

    public function __invoke(AttributeHandler $handler): callable
    {
        return function () use ($handler) {
            $this->value = $handler();

            $object = $handler->getObject();
            /** @var ReflectionProperty $ref */
            $ref = $handler->getReflector();

            $class = $this->wrap;

            $ref->setValue($object, $v = new $class($this->value));

            return $v;
        };
    }
}
