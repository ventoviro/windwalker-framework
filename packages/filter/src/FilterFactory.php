<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter;

use Windwalker\Data\Collection;
use Windwalker\Filter\Rule\Absolute;
use Windwalker\Filter\Rule\Alnum;
use Windwalker\Filter\Rule\CastTo;
use Windwalker\Filter\Rule\Cmd;
use Windwalker\Filter\Rule\DefaultValue;
use Windwalker\Filter\Rule\EmailAddress;
use Windwalker\Filter\Rule\IPAddress;
use Windwalker\Filter\Rule\IPV4;
use Windwalker\Filter\Rule\IPV6;
use Windwalker\Filter\Rule\Length;
use Windwalker\Filter\Rule\Negative;
use Windwalker\Filter\Rule\Range;
use Windwalker\Filter\Rule\RawValue;
use Windwalker\Filter\Rule\Regex;
use Windwalker\Filter\Rule\Required;
use Windwalker\Filter\Rule\UrlAddress;
use Windwalker\Filter\Rule\Words;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Classes\ObjectBuilderAwareTrait;
use Windwalker\Utilities\TypeCast;

/**
 * The FilterFactory class.
 */
class FilterFactory
{
    use ObjectBuilderAwareTrait;

    /**
     * @var callable[]
     */
    protected array $factories = [];

    /**
     * FilterFactory constructor.
     *
     * @param  callable[]  $factories
     */
    public function __construct(?array $factories = null)
    {
        if ($factories !== null) {
            $this->factories = $factories;
        } else {
            $this->prepareDefaultFactories();
        }
    }

    public function createNested(array $map, array $options = []): NestedFilter
    {
        return new NestedFilter($this->createMap($map, $options));
    }

    public function createMap(array $map, array $options = []): array
    {
        return Arr::mapRecursive($map, fn ($syntax) => $this->createChainFromSyntax($syntax, $options));
    }

    /**
     * createChainFromSyntax
     *
     * @param  string|callable|FilterInterface|ValidatorInterface|array  $syntax
     * @param  array                                                     $options
     *
     * @return  FilterInterface|ValidatorInterface
     */
    public function createChainFromSyntax(mixed $syntax, array $options = []): FilterInterface|ValidatorInterface
    {
        if (is_string($syntax)) {
            $clauses = Arr::explodeAndClear(';', $syntax);
        } elseif (!is_array($syntax)) {
            $clauses = [$syntax];
        } else {
            $clauses = $syntax;
        }

        $chain = new ChainFilter();

        foreach ($clauses as $clause) {
            if (is_string($clause)) {
                $filter = $this->createFromSyntax($clause, $options);
            } else {
                $filter = $this->create($clause);
            }

            $chain->addFilter($filter);
        }

        return $chain;
    }

    public function createFromSyntax(string $syntax, array &$options = []): FilterInterface|ValidatorInterface
    {
        [$type, $optString] = array_pad(explode(':', $syntax), 2, '');

        $type = trim($type);

        preg_match_all('/(\w+)(\s?=\s?(\w+))?/', $optString, $matches, PREG_SET_ORDER);

        $options = [];
        foreach ($matches as $match) {
            if (isset($match[1])) {
                $key = $match[1];
                $value = $match[3] ?? true;

                $options[$key] = $value;
            }
        }

        return $this->createByFactory($type, $options);
    }

    public function create($type, ...$args): FilterInterface|ValidatorInterface
    {
        if ($type instanceof FilterInterface || $type instanceof ValidatorInterface) {
            return $type;
        }

        if (is_callable($type)) {
            return new CallbackFilter($type);
        }

        if (class_exists($type)) {
            return $this->getObjectBuilder()->createObject($type, ...$args);
        }

        if ($args === []) {
            return $this->createChainFromSyntax($type);
        }

        return $this->createFromSyntax($type, ...$args);
    }

    public function createByFactory(string $type, array $options = []): FilterInterface|ValidatorInterface
    {
        return $this->create($this->getFactory($type)($options));
    }

    public function getFactory(string $type): callable
    {
        if (!isset($this->factories[$type])) {
            throw new \OutOfRangeException("Filter type: $type not found.");
        }

        return $this->factories[$type];
    }

    /**
     * addFactory
     *
     * @param  string    $type
     * @param  callable  $factory
     *
     * @return  $this
     */
    public function addFactory(string $type, callable|string $factory)
    {
        if (is_string($factory)) {
            $factory = fn () => new $factory();
        }

        $this->factories[$type] = $factory;

        return $this;
    }

    /**
     * removeFactory
     *
     * @param  string  $type
     *
     * @return  $this
     */
    public function removeFactory(string $type)
    {
        unset($this->factories[$type]);

        return $this;
    }

    /**
     * @return callable[]
     */
    public function getFactories(): array
    {
        return $this->factories;
    }

    /**
     * @param  callable[]  $factories
     *
     * @return  static  Return self to support chaining.
     */
    public function setFactories(array $factories)
    {
        $this->factories = $factories;

        return $this;
    }

    protected function prepareDefaultFactories(): void
    {
        $this->addFactory('abs', Absolute::class);
        $this->addFactory('alnum', Alnum::class);
        $this->addFactory('cmd', Cmd::class);
        $this->addFactory('email', EmailAddress::class);
        $this->addFactory('url', UrlAddress::class);
        $this->addFactory('words', Words::class);
        $this->addFactory('ip', IPAddress::class);
        $this->addFactory('ipv4', IPV4::class);
        $this->addFactory('ipv6', IPV6::class);
        $this->addFactory('neg', Negative::class);
        $this->addFactory('raw', RawValue::class);
        $this->addFactory(
            'range',
            fn(array $options) => new Range(
                TypeCast::tryNumeric($options['min'] ?? null, true),
                TypeCast::tryNumeric($options['max'] ?? null, true)
            )
        );
        $this->addFactory(
            'length',
            fn(array $options) => new Length(
                TypeCast::tryInteger($options['max'] ?? null, true),
                $options['utf8'] ?? true
            )
        );
        $this->addFactory(
            'regex',
            fn(array $options) => new Regex(
                $options['regex'] ?? '',
                $options['type'] ?? Regex::TYPE_MATCH,
            )
        );
        $this->addFactory('required', Required::class);
        $this->addFactory('default', fn (array $options) => new DefaultValue(array_key_first($options)));
        $this->addFactory('func', fn (array $options) => new CallbackFilter(array_key_first($options)));


        // types
        $types = [
            'string',
            'int',
            'float',
            'array',
            'bool',
            'object'
        ];

        foreach ($types as $type) {
            $this->addFactory($type, fn(array $options) => new CastTo($type, (bool) ($options['strict'] ?? false)));
        }
    }
}
