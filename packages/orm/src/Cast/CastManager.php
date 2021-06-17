<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;
use Windwalker\Utilities\Cache\InstanceCacheTrait;
use Windwalker\Utilities\TypeCast;

/**
 * The CastManager class.
 */
class CastManager
{
    use InstanceCacheTrait;

    /**
     * @var array<int, array<int, array<int, callable|int>>>
     */
    protected array $castGroups = [];

    /**
     * @var array<int, mixed>
     */
    protected array $castAliases = [];

    /**
     * CastManager constructor.
     */
    public function __construct()
    {
        $this->prepareDefaultAliases();
    }

    /**
     * Add a custom cast type, the field must be DB field name.
     *
     * @param  string      $field
     * @param  mixed       $cast
     * @param  mixed|null  $extract
     * @param  int         $options
     *
     * @return  static
     */
    public function addCast(
        string $field,
        mixed $cast,
        mixed $extract = null,
        int $options = 0
    ): static {
        $this->castGroups[$field] ??= [];

        $this->castGroups[$field][] = [$cast, $extract, $options];

        return $this;
    }

    /**
     * getCast
     *
     * @param  string  $field
     *
     * @return  array<array<callable>>
     */
    public function getFieldCasts(string $field): array
    {
        return $this->once(
            'casts:' . $field,
            function () use ($field) {
                $groups = $this->castGroups[$field] ?? [];
                $casts = [];

                foreach ($groups as $castControl) {
                    [$cast, $extract, $options] = $castControl;

                    if (!$extract) {
                        if ($cast instanceof CastInterface || is_subclass_of($cast, CastInterface::class)) {
                            $extract = $cast;
                        } else {
                            $extract = [TypeCast::class, 'tryString'];
                        }
                    }

                    $casts[] = [
                        $this->castToCallback($cast, $options, 'cast'),
                        $this->castToCallback($extract, $options, 'extract')
                    ];
                }

                return $casts;
            }
        );
    }

    /**
     * @param  array  $castGroups
     *
     * @return  static  Return self to support chaining.
     */
    public function setCastGroups(array $castGroups): static
    {
        $this->castGroups = $castGroups;

        return $this;
    }

    public function alias(string $castName, mixed $alias): static
    {
        $this->castAliases[$castName] = $alias;

        return $this;
    }

    public function removeAlias(string $castName): static
    {
        unset($this->castAliases[$castName]);

        return $this;
    }

    public function setAliases(array $aliases): static
    {
        $this->castAliases = $aliases;

        return $this;
    }

    public function resolveAlias(string $castName): mixed
    {
        while (isset($this->castAliases[$castName])) {
            $castName = $this->castAliases[$castName];
        }

        return $castName;
    }

    /**
     * castToCallback
     *
     * @param  mixed   $cast
     * @param  int     $options
     * @param  string  $direction
     *
     * @return  callable
     */
    public function castToCallback(mixed $cast, int $options, $direction = 'cast'): callable
    {
        if ($cast === null) {
            return fn(mixed $value) => $value;
        }

        if (is_callable($cast)) {
            return fn(mixed $value) => $cast($value);
        }

        if (is_string($cast)) {
            $cast = $this->resolveAlias($cast);

            if (class_exists($cast)) {
                // Cast interface
                if (is_subclass_of($cast, CastInterface::class)) {
                    return function (mixed $value, ORM $orm) use ($direction, $cast) {
                        $castObject = $orm->getAttributesResolver()->createObject($cast);

                        return $castObject->$direction($value);
                    };
                }

                // Pure class
                return static function (mixed $value, ORM $orm) use ($options, $cast) {
                    if ($value === null && $options & Cast::NULLABLE) {
                        return $value;
                    }

                    if (!($options & Cast::USE_HYDRATOR) && !($options & Cast::USE_CONSTRUCTOR)) {
                        $options |= EntityMetadata::isEntity($cast)
                            ? Cast::USE_HYDRATOR
                            : Cast::USE_CONSTRUCTOR;
                    }

                    if ($options & Cast::USE_HYDRATOR) {
                        $object = $orm->getAttributesResolver()->createObject($cast);

                        $value = TypeCast::toArray($value);

                        if (EntityMetadata::isEntity($object)) {
                            return $orm->getEntityHydrator()->hydrate($value, $object);
                        }

                        return $orm->getDb()->getHydrator()->hydrate($value, $object);
                    }

                    return $orm->getAttributesResolver()->createObject($cast, $value);
                };
            }

            return static fn(mixed $value) => TypeCast::try($value, $cast);
        }

        if (is_object($cast)) {
            // Cast interface
            if ($cast instanceof CastInterface) {
                return [$cast, $direction];
            }

            // Pure object
            return static fn(mixed $value, ORM $orm) => $orm->getDb()
                ->getHydrator()
                ->hydrate($value, $cast);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Unsupported cast type: %s',
                get_debug_type($cast)
            )
        );
    }

    protected function prepareDefaultAliases(): void
    {
        $this->alias(
            'datetime',
            DateTimeCast::class
        );

        $this->alias(
            'timestamp',
            TimestampCast::class
        );

        $this->alias(
            'json',
            JsonCast::class
        );
    }
}
