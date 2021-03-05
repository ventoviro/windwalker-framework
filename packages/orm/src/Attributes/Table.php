<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\ORM\EntityMapper;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\StrInflector;

/**
 * The Table class.
 */
#[\Attribute]
class Table
{
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
}
