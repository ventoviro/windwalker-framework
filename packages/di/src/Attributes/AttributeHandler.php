<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use Reflector;
use Windwalker\DI\Container;

/**
 * The AttributeHandler class.
 */
class AttributeHandler extends \Windwalker\Attributes\AttributeHandler
{
    /**
     * @inheritDoc
     */
    public function __construct(
        callable $handler,
        Reflector $reflector,
        ?object $object,
        AttributesResolver $resolver,
        protected Container $container
    ) {
        parent::__construct($handler, $reflector, $object, $resolver);
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @return AttributesResolver
     */
    public function getResolver(): AttributesResolver
    {
        return $this->resolver;
    }
}
