<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\StrInflector;

/**
 * The Table class.
 */
#[Attribute]
class Table implements AttributeInterface
{
    use ORMAttributeTrait;
    use OptionAccessTrait;

    protected array $defaultOptions = [];

    /**
     * Table constructor.
     *
     * @param  string       $name
     * @param  string|null  $alias
     * @param  string       $mapperClass
     * @param  array        $options
     */
    public function __construct(
        protected string $name,
        protected ?string $alias = null,
        protected string $mapperClass = EntityMapper::class,
        array $options = [],
    ) {
        $this->prepareOptions($this->defaultOptions, $options);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias ?? StrInflector::toSingular($this->name);
    }

    /**
     * @return string
     */
    public function getMapperClass(): string
    {
        return $this->mapperClass;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        $table = $this;

        $setter = function () use ($table) {
            $this->tableName = $table->getName();
            $this->tableAlias = $table->getAlias();
            $this->mapperClass = $table->getMapperClass();
        };

        $setter->call($metadata);

        $metadata->setOptions($this->getOptions());

        return $handler->get();
    }
}
