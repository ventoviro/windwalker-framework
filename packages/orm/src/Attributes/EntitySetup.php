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
use Windwalker\Attributes\AttributeInterface;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The EntitySetup class.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class EntitySetup extends AbstractORMAttribute
{
    /**
     * @inheritDoc
     */
    public function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        $metadata->addAttributeMap(static::class, $handler->getReflector());

        return function () use ($handler, $metadata) {
            $handler->getResolver()
                ->call(
                    $method = $handler(),
                    [
                        'metadata' => $metadata,
                        static::class => $metadata,
                    ]
                );

            return $method;
        };
    }
}
