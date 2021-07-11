<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use stdClass;
use Traversable;
use Windwalker\Utilities\Classes\PreventInitialTrait;
use Windwalker\Utilities\Contract\DumpableInterface;

/**
 * The TypeCast class.
 *
 * @method static int|null tryInteger($value, bool $strict = false)
 * @method static float|null tryFloat($value, bool $strict = false)
 * @method static int|float|null tryNumeric($value, bool $strict = false)
 * @method static string|null tryString($value, bool $strict = false)
 * @method static bool|null tryBoolean($value, bool $strict = false)
 * @method static array|null tryArray($value, bool $strict = false)
 * @method static object|null tryObject($value, bool $strict = false)
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class TypeCast
{
    use PreventInitialTrait;

    public const TYPE_INT = 'int';

    public const TYPE_FLOAT = 'float';

    public const TYPE_STRING = 'string';

    public const TYPE_BOOL = 'bool';

    public const TYPE_ARRAY = 'array';

    public const TYPE_OBJECT = 'object';

    /**
     * Utility function to convert all types to an array.
     *
     * @param  mixed  $data          The data to convert.
     * @param  bool   $recursive     Recursive if data is nested.
     * @param  bool   $onlyDumpable  Objects only implements DumpableInterface will convert to array.
     *
     * @return  array  The converted array.
     */
    public static function toArray(mixed $data, bool $recursive = false, bool $onlyDumpable = false): array
    {
        // Ensure the input data is an array.
        if ($data instanceof DumpableInterface) {
            $data = $data->dump($recursive);

            if ($recursive) {
                return $data;
            }
        } elseif ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        } else {
            $data = (array) $data;
        }

        if ($recursive) {
            foreach ($data as $k => $value) {
                if (is_array($value)) {
                    $data[$k] = static::toArray($value, $recursive, $onlyDumpable);
                } elseif (is_object($value)) {
                    if ($onlyDumpable && $value instanceof DumpableInterface) {
                        $data[$k] = static::toArray($value, $recursive, $onlyDumpable);
                    } elseif (!$onlyDumpable) {
                        $data[$k] = static::toArray($value, $recursive, $onlyDumpable);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * toIterable
     *
     * @param  mixed  $iterable
     *
     * @return  iterable
     *
     * @since  3.5
     */
    #[Pure]
    public static function toIterable(
        mixed $iterable
    ): iterable {
        if (is_iterable($iterable)) {
            return $iterable;
        }

        if (is_object($iterable)) {
            return get_object_vars($iterable);
        }

        return (array) $iterable;
    }

    /**
     * Utility function to map an array to a stdClass object.
     *
     * @param  array   $array      The array to map.
     * @param  bool    $recursive  Recursive.
     * @param  string  $class      Name of the class to create
     *
     * @return  object  The object mapped from the given array
     *
     * @since   2.0
     */
    public static function toObject(array $array, bool $recursive = false, string $class = stdClass::class): object
    {
        $obj = new $class();

        foreach ($array as $k => $v) {
            if (is_array($v) && $recursive) {
                $obj->$k = static::toObject($v, $recursive, $class);
            } else {
                $obj->$k = $v;
            }
        }

        return $obj;
    }

    /**
     * Convert all to string.
     *
     * @param  mixed  $data  The data to convert.
     * @param  bool   $dump  If is array or object, will dump it if this argument set to TRUE.
     *
     * @return  string
     *
     * @since  3.5
     */
    public static function toString(mixed $data, bool $dump = false): string
    {
        if ($data instanceof Closure) {
            return static::toString($data());
        }

        if (is_stringable($data)) {
            return (string) $data;
        }

        if (is_array($data)) {
            $data = $dump ? Arr::dump($data) : 'Array()';
        }

        if (is_object($data)) {
            $data = $dump ? Arr::dump($data) : sprintf('[Object %s]', get_class($data));
        }

        return (string) $data;
    }

    /**
     * forceString
     *
     * @param  mixed  $data  The data to convert.
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function forceString(mixed $data): string
    {
        return static::toString($data, true);
    }

    /**
     * mapAs
     *
     * @param  array   $src
     * @param  string  $typeOrClass
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function mapAs(array $src, string $typeOrClass): array
    {
        if (class_exists($typeOrClass)) {
            return array_map(
                static function ($value) use ($typeOrClass) {
                    return new $typeOrClass($value);
                },
                $src
            );
        }

        $typeOrClass = strtolower($typeOrClass);

        if ($typeOrClass === 'array') {
            return array_map(
                [static::class, 'toArray'],
                $src
            );
        }

        if ($typeOrClass === 'string') {
            return array_map(
                static function ($value) {
                    return (string) $value;
                },
                $src
            );
        }

        if ($typeOrClass === 'int' || $typeOrClass === 'integer') {
            return array_map(
                static function ($value) {
                    return (int) $value;
                },
                $src
            );
        }

        if ($typeOrClass === 'float' || $typeOrClass === 'double') {
            return array_map(
                static function ($value) {
                    return (float) $value;
                },
                $src
            );
        }

        if ($typeOrClass === 'bool' || $typeOrClass === 'boolean') {
            return array_map(
                static function ($value) {
                    return (bool) $value;
                },
                $src
            );
        }

        throw new InvalidArgumentException(sprintf('%s is not a valid type or class name.', $typeOrClass));
    }

    /**
     * Try convert to another type or return NULL if unable to cast.
     *
     * @see    https://wiki.php.net/rfc/safe_cast
     *
     * @param  mixed   $value
     * @param  string  $type
     * @param  bool    $strict
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function try(mixed $value, string $type, bool $strict = false): mixed
    {
        switch (strtolower($type)) {
            case 'int':
            case 'integer':
                if ($strict) {
                    return is_numeric($value) && floor((float) $value) == $value ? (int) $value : null;
                }

                if (is_scalar($value)) {
                    return (int) $value;
                }

                return null;

            case 'float':
            case 'double':
            case 'real':
                if ($strict) {
                    return is_numeric($value) ? (float) $value : null;
                }

                if (is_scalar($value)) {
                    return (float) $value;
                }

                return null;

            case 'number':
            case 'numeric':
                // int
                if (is_numeric($value)) {
                    if (floor((float) $value) == $value) {
                        return static::tryInteger($value, $strict);
                    }

                    return static::tryFloat($value, $strict);
                }

                return $strict ? null : (int) $value;

            case 'string':
                if ($strict && ($value === null || is_bool($value))) {
                    return null;
                }

                return is_stringable($value) ? (string) $value : null;

            case 'bool':
            case 'boolean':
                return (bool) $value;

            case 'array':
                return (array) $value;

            case 'object':
                return (object) $value;

            default:
                return null;
        }
    }

    /**
     * __callStatic
     *
     * @param  string  $name
     * @param  array   $args
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function __callStatic(string $name, array $args): mixed
    {
        $tryMethods = [
            'tryInteger',
            'tryFloat',
            'tryNumeric',
            'tryString',
            'tryBoolean',
            'tryArray',
            'tryObject',
        ];

        if (in_array(strtolower($name), array_map('strtolower', $tryMethods), true)) {
            return static::try($args[0], strtolower(substr($name, 3)), $args[1] ?? false);
        }

        throw new BadMethodCallException(
            sprintf(
                'Method: %s::%s() not found',
                static::class,
                $name
            )
        );
    }
}
