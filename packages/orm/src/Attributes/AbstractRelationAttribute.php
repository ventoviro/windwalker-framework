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
use Windwalker\ORM\Relation\RelationManager;
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

/**
 * The AbstractRelationAttribute class.
 */
abstract class AbstractRelationAttribute implements AttributeInterface
{
    use ORMAttributeTrait;

    protected array $columns;

    /**
     * OneToOne constructor.
     *
     * @param  string|null  $target
     * @param  mixed        ...$columns
     */
    public function __construct(
        protected ?string $target = null,
        ...$columns
    ) {
        $this->columns = $columns;
    }

    /**
     * @inheritDoc
     */
    protected function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        /** @var \ReflectionProperty $prop */
        $prop = $handler->getReflector();
        $rm   = $metadata->getRelationManager();
        $rel  = $this->createRelation($rm, $prop);

        $attrs = $prop->getAttributes(RelationConfigureAttributeInterface::class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attrs as $attr) {
            /** @var RelationConfigureAttributeInterface $attrInstance */
            $attrInstance = $attr->newInstance();

            $attrInstance($rel);
        }

        return $handler->get();
    }

    abstract protected function createRelation(
        RelationManager $rm,
        \ReflectionProperty $prop
    ): RelationConfigureInterface;
}
