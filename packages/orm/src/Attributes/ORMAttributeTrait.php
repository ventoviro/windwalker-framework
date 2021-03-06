<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\Attributes\AttributeHandler;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The AbstractORMAttribute class.
 */
trait ORMAttributeTrait
{
    /**
     * @inheritDoc
     */
    public function __invoke(AttributeHandler $handler): callable
    {
        /** @var EntityMetadata $metadata */
        $metadata = $handler->getResolver()->getOption('metadata');

        // Not in setup process, return.
        if (!$metadata) {
            return $handler->get();
        }

        return $this->handle($metadata, $handler);
    }

    /**
     * Run ORM attribute handler.
     *
     * @param  EntityMetadata    $metadata
     * @param  AttributeHandler  $handler
     *
     * @return  callable
     */
    abstract protected function handle(EntityMetadata $metadata, AttributeHandler $handler): callable;
}
