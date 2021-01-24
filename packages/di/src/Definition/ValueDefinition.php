<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use Windwalker\DI\Container;

/**
 * The ValueDefinition class.
 */
class ValueDefinition implements DefinitionInterface
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * ValueDefinition constructor.
     *
     * @param $value
     */
    public function __construct($value)
    {
        $this->set($value);
    }

    /**
     * resolve
     *
     * @param  Container  $container
     *
     * @return mixed
     */
    public function resolve(Container $container): mixed
    {
        return $this->value;
    }

    /**
     * Set new value or factory callback to this definition.
     *
     * @param  mixed  $value  Value or callable.
     *
     * @return  void
     */
    public function set(mixed $value): void
    {
        $this->value = $value;
    }
}
