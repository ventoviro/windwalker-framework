<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

/**
 * Trait ClassDecorator
 */
class ClassDecorator
{
    protected ?object $innerObject = null;

    /**
     * DecoratorTrait constructor.
     *
     * @param  object  $innerObject
     */
    public function __construct(object $innerObject)
    {
        $this->innerObject = $innerObject;
    }

    public function __call(string $name, array $args): mixed
    {
        return $this->innerObject->$name(...$args);
    }
}
