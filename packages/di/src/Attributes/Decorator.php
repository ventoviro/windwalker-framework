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

/**
 * The Decorator class.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Decorator implements ContainerAttributeInterface
{
    protected string $class;

    protected array $args;

    /**
     * Decorator constructor.
     *
     * @param  string  $class
     * @param  mixed   ...$args
     */
    public function __construct(string $class, ...$args)
    {
        $this->class = $class;
        $this->args = $args;
    }

    public function __invoke(AttributeHandler $handler): callable
    {
        return fn(...$args) => $handler->getContainer()
            ->newInstance($this->class, [$handler(...$args), ...$this->args]);
    }
}
