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
use Windwalker\Cache\Exception\LogicException;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The Cast class.
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_PROPERTY)]
class Cast implements AttributeInterface
{
    use ORMAttributeTrait;

    public const NULLABLE = 1 << 0;
    public const USE_CONSTRUCTOR = 1 << 1;
    public const USE_HYDRATOR = 1 << 2;

    protected mixed $cast;

    protected int $options;

    /**
     * @var mixed
     */
    protected $extract;

    /**
     * Cast constructor.
     *
     * @param  string      $cast
     * @param  mixed|null  $extract
     * @param  int         $options
     */
    public function __construct(mixed $cast, mixed $extract = null, int $options = 0)
    {
        $this->cast    = $cast;
        $this->options = $options;
        $this->extract = $extract;
    }

    /**
     * @return mixed
     */
    public function getCast(): mixed
    {
        return $this->cast;
    }

    /**
     * @return mixed
     */
    public function getExtract(): mixed
    {
        return $this->extract;
    }

    /**
     * @return int
     */
    public function getOptions(): int
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        /** @var \ReflectionProperty $prop */
        $prop = $handler->getReflector();

        $column = $metadata->getColumnByPropertyName($prop->getName());

        $colName = $column ? $column->getName() : $prop->getName();

        $metadata->cast(
            $colName,
            $this->getCast(),
            $this->getExtract(),
            $this->getOptions()
        );

        return $handler->get();
    }
}
