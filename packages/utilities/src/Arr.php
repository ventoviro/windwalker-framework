<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use InvalidArgumentException;
use ReflectionObject;
use Windwalker\Utilities\Classes\PreventInitialTrait;
use Windwalker\Utilities\Compare\WhereWrapper;
use Windwalker\Utilities\Dumper\VarDumper;

use function Windwalker\count;

/**
 * The ArrayHelper class
 *
 * @since  2.0
 */
abstract class Arr
{
    use PreventInitialTrait;
    use ArrConverterTrait;

    public const GROUP_TYPE_ARRAY = 1;

    public const GROUP_TYPE_KEY_BY = 2;

    public const GROUP_TYPE_MIX = 3;

    /**
     * Output mock to support test.
     *
     * @var  resource
     */
    public static $output;

    /**
     * Check a key exists in object or array. The key can be a path separated by dots.
     *
     * @param  array|object  $source     Object or array to check.
     * @param  string|int    $key        The key path name.
     * @param  string        $delimiter  The separator to split paths.
     *
     * @return  bool
     *
     * @since 4.0
     */
    public static function has(object|array $source, int|string $key, string $delimiter = '.'): bool
    {
        $nodes = static::getPathNodes((string) $key, $delimiter);

        if (empty($nodes)) {
            return false;
        }

        $key   = array_shift($nodes);
        $value = null;

        if ($source instanceof ArrayAccess && isset($source[$key])) {
            $value = $source[$key];
        } elseif (is_array($source) && array_key_exists($key, $source)) {
            $value = $source[$key];
        } elseif (is_object($source) && property_exists($source, $key)) {
            $value = $source->$key;
        } else {
            return false;
        }

        if ($nodes !== [] && (is_array($value) || is_object($value))) {
            return static::has($value, implode($delimiter, $nodes));
        }

        return true;
    }

    /**
     * Set a default value to array or object if not exists. Index can be a path separated by dots.
     *
     * @param  array|object  $array      Object or array to set default value.
     * @param  string        $key        Index path name.
     * @param  mixed         $value      Value to set if not exists.
     * @param  string        $delimiter  Separator to split paths.
     *
     * @return  array|object
     * @throws InvalidArgumentException
     * @since 4.0
     */
    public static function def(object|array $array, string $key, mixed $value, string $delimiter = '.'): object|array
    {
        if (static::has($array, $key, $delimiter)) {
            return $array;
        }

        return static::set($array, $key, $value, $delimiter);
    }

    /**
     * getPathNodes
     *
     * @param  string|array  $path
     * @param  string        $delimiter
     *
     * @return  array
     */
    private static function getPathNodes(string|array $path, string $delimiter = '.'): array
    {
        if ($delimiter === '') {
            return [$path];
        }

        if (is_array($path)) {
            return $path;
        }

        if ($path && !str_contains((string) $path, $delimiter)) {
            return [$path];
        }

        return array_values(array_filter(explode($delimiter, (string) $path), 'strlen'));
    }

    public static function explodeAndClear(string $delimiter, string $string, ?int $limit = null): array
    {
        return array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(...func_get_args()),
                ),
                'strlen'
            )
        );
    }

    /**
     * Get data from array or object by path.
     *
     * Example: `ArrayHelper::get($array, 'foo.bar.yoo')` equals to $array['foo']['bar']['yoo'].
     *
     * @param  mixed       $data       An array or object to get value.
     * @param  string|int  $key        The key path.
     * @param  string      $delimiter  Separator of paths.
     *
     * @return mixed Found value, null if not exists.
     *
     * @since   2.0
     */
    public static function &get(mixed &$data, int|string $key, string $delimiter = '.'): mixed
    {
        $nodes = static::getPathNodes((string) $key, $delimiter);
        $null  = null;

        if (empty($nodes)) {
            return $null;
        }

        $dataTmp = &$data;

        foreach ($nodes as $arg) {
            if (static::isAccessible($dataTmp) && isset($dataTmp[$arg])) {
                // Check arrayAccess value exists
                $dataTmp = &$dataTmp[$arg];
            } elseif (is_object($dataTmp) && isset($dataTmp->$arg)) {
                // Check object value exists
                $dataTmp = &$dataTmp->$arg;
            } else {
                return $null;
            }
        }

        return $dataTmp;
    }

    /**
     * Set value into array or object. The key can be path type.
     *
     * @param  mixed   $data       An array or object to set data.
     * @param  string  $key        Path name separate by dot.
     * @param  mixed   $value      Value to set into array or object.
     * @param  string  $delimiter  Separator to split path.
     * @param  string  $storeType  The new store data type, default is `array`. you can set object class name.
     *
     * @return  array|object
     * @throws InvalidArgumentException
     * @since   2.0
     */
    public static function set(
        mixed $data,
        string $key,
        mixed $value,
        string $delimiter = '.',
        string $storeType = 'array'
    ): object|array {
        $nodes = static::getPathNodes((string) $key, $delimiter);

        if (empty($nodes)) {
            return $data;
        }

        /**
         * A closure as inner function to create data store.
         *
         * @param  string  $type  Type name.
         *
         * @return  array
         *
         * @throws InvalidArgumentException
         */
        $createStore = static function (string $type) {
            if (strtolower($type) === 'array') {
                return [];
            }

            if (class_exists($type)) {
                return new $type();
            }

            throw new InvalidArgumentException(sprintf('Type or class: %s not exists', $type));
        };

        $dataTmp = &$data;

        foreach ($nodes as $node) {
            if (is_object($dataTmp)) {
                // If this node not exists, create new one.
                if (empty($dataTmp->$node)) {
                    $dataTmp->$node = $createStore($storeType);
                }

                $dataTmp = &$dataTmp->$node;
            } elseif (is_array($dataTmp)) {
                // If this node not exists, create new one.
                if (empty($dataTmp[$node])) {
                    $dataTmp[$node] = $createStore($storeType);
                }

                $dataTmp = &$dataTmp[$node];
            } else {
                // If a node is value but path is not go to the end, we replace this value as a new store.
                // Then next node can insert new value to this store.
                $dataTmp = &$createStore($storeType);
            }
        }

        // Now, path go to the end, means we get latest node, set value to this node.
        $dataTmp = $value;

        return $data;
    }

    /**
     * Remove a value from array or object. The key can be a path separated by dots.
     *
     * @param  array|object  $data       Object or array to remove value.
     * @param  string|int    $key        The key path name.
     * @param  string        $delimiter  The separator to split paths.
     *
     * @return  array|object
     */
    public static function remove(object|array $data, int|string $key, $delimiter = '.'): object|array
    {
        $nodes = static::getPathNodes((string) $key, $delimiter);

        if (!count($nodes)) {
            return $data;
        }

        $node     = null;
        $previous = null;
        $dataTmp  = &$data;

        foreach ($nodes as $node) {
            if (is_object($dataTmp)) {
                if (empty($dataTmp->$node)) {
                    return $data;
                }

                $previous = &$dataTmp;
                $dataTmp  = &$dataTmp->$node;
            } elseif (is_array($dataTmp)) {
                if (empty($dataTmp[$node])) {
                    return $data;
                }

                $previous = &$dataTmp;
                $dataTmp  = &$dataTmp[$node];
            } else {
                return $data;
            }
        }

        // Now, path go to the end, means we get latest node, unset value to this node.
        if (is_object($previous)) {
            unset($previous->$node);
        } elseif (is_array($previous)) {
            unset($previous[$node]);
        }

        return $data;
    }

    /**
     * Collapse array to one dimension
     *
     * @param  array|object  $data
     * @param  bool          $keepKey
     *
     * @return  array
     */
    public static function collapse(array|object $data, $keepKey = false): array
    {
        $result = [];

        array_walk_recursive(
            $data,
            static function ($v, $k) use ($keepKey, &$result) {
                if ($keepKey) {
                    $result[$k] = $v;
                } else {
                    $result[] = $v;
                }
            }
        );

        return $result;
    }

    /**
     * Method to recursively convert data to one dimension array.
     *
     * @param  array|object  $array      The array or object to convert.
     * @param  string        $delimiter  The key path delimiter.
     * @param  int           $depth      Only flatten limited depth, 0 means on limit.
     * @param  string|null   $prefix     Last level key prefix.
     *
     * @return array
     */
    public static function flatten(
        object|array $array,
        string $delimiter = '.',
        int $depth = 0,
        ?string $prefix = null
    ): array {
        $temp = [];

        foreach (TypeCast::toArray($array, false) as $k => $v) {
            $key = $prefix !== null ? $prefix . $delimiter . $k : $k;

            if (($depth === 0 || $depth > 1) && is_array($v)) {
                $temp[] = static::flatten($v, $delimiter, $depth === 0 ? $depth : $depth - 1, (string) $key);
            } else {
                $temp[] = [$key => $v];
            }
        }

        // Prevent resource-greedy loop.
        // @see https://github.com/dseguy/clearPHP/blob/master/rules/no-array_merge-in-loop.md
        if (count($temp)) {
            return array_merge(...$temp);
        }

        return [];
    }

    /**
     * keep
     *
     * @param  array|object  $data
     * @param  array         $fields
     *
     * @return  array|object
     * @throws InvalidArgumentException
     */
    public static function only(object|array $data, array $fields): object|array
    {
        if (is_array($data)) {
            return array_intersect_key($data, array_flip($fields));
        }

        if (is_object($data)) {
            $keeps = array_keys(array_diff_key(get_object_vars($data), array_flip($fields)));

            foreach ($keeps as $key) {
                if (property_exists($data, $key)) {
                    unset($data->$key);
                }
            }

            return $data;
        }

        throw new InvalidArgumentException('Argument 1 not array or object');
    }

    /**
     * except
     *
     * @param  array|object  $data
     * @param  array         $fields
     *
     * @return  array|object
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function except(object|array $data, array $fields): object|array
    {
        if (is_array($data)) {
            return array_diff_key($data, array_flip($fields));
        }

        if (is_object($data)) {
            foreach ($fields as $key) {
                if (property_exists($data, $key)) {
                    unset($data->$key);
                }
            }

            return $data;
        }

        throw new InvalidArgumentException('Argument 1 not array or object');
    }

    /**
     * find
     *
     * @param  array          $data
     * @param  callable|null  $callback
     * @param  bool           $keepKey
     * @param  int|null       $offset
     * @param  int|null       $limit
     *
     * @return array
     */
    public static function find(
        array $data,
        ?callable $callback = null,
        bool $keepKey = false,
        ?int $offset = null,
        ?int $limit = null
    ): array {
        $results = [];
        $i       = 0;
        $c       = 0;

        $callback ??= 'is_null';

        foreach ($data as $key => $value) {
            // If use global function, send only value as argument.
            if (is_string($callback)) {
                $r = $callback($value);
            } else {
                $r = $callback($value, $key);
            }

            if ($r) {
                if ($offset !== null && $offset > $i) {
                    continue;
                }

                if ($limit !== null && $c >= $limit) {
                    break;
                }

                $results[$key] = $value;
                $c++;
            }

            $i++;
        }

        return $keepKey ? $results : array_values($results);
    }

    /**
     * findFirst
     *
     * @param  array     $data
     * @param  callable  $callback
     *
     * @return  mixed
     */
    public static function findFirst(array $data, callable $callback = null): mixed
    {
        $results = static::find($data, $callback, false, 0, 1);

        return count($results) ? $results[0] : null;
    }

    /**
     * reject
     *
     * @param  array     $data
     * @param  callable  $callback
     * @param  bool      $keepKey
     *
     * @return  array
     */
    public static function reject(array $data, callable $callback, bool $keepKey = false): array
    {
        return static::find(
            $data,
            static function (&$value, &$key) use ($callback) {
                if (is_string($callback)) {
                    return !$callback($value);
                }

                return !$callback($value, $key);
            },
            $keepKey
        );
    }

    /**
     * takeout
     *
     * @param  array|object  $data
     * @param  string|int    $key
     * @param  mixed         $default
     * @param  string        $delimiter
     *
     * @return  mixed
     */
    public static function takeout(
        object|array &$data,
        int|string $key,
        $default = null,
        string $delimiter = '.'
    ): mixed {
        if (!static::has($data, $key, $delimiter)) {
            return $default;
        }

        $value = static::get($data, $key);

        $data = static::remove($data, $key, $delimiter);

        return $value;
    }

    /**
     * sort
     *
     * @param  array            $data
     * @param  callable|string  $condition
     * @param  bool             $descending
     * @param  int              $options
     *
     * @return  array
     *
     * @since   4.0
     */
    public static function sort(
        array $data,
        callable|string $condition,
        bool $descending = false,
        int $options = SORT_REGULAR
    ): array {
        $results = [];

        // If condition is string, we just use this as key name to get sort data from items.
        $callback = is_callable($condition) ? $condition : static function ($item) use ($condition) {
            // We don't know child item is array or object, use getter to get it.
            return static::get($item, $condition);
        };

        // Loop items to get sorting conditions with defined in callback.
        foreach ($data as $key => $value) {
            $results[$key] = $callback($value, $key);
        }

        // Do sort
        $descending ? arsort($results, $options) : asort($results, $options);

        // Get origin data from items
        foreach ($results as $key => $value) {
            $results[$key] = $data[$key];
        }

        return $results;
    }

    /**
     * Takes an associative array of arrays and inverts the array keys to values using the array values as keys.
     *
     * Example:
     * $input = array(
     *     'New' => array('1000', '1500', '1750'),
     *     'Used' => array('3000', '4000', '5000', '6000')
     * );
     * $output = ArrayHelper::invert($input);
     *
     * Output would be equal to:
     * $output = array(
     *     '1000' => 'New',
     *     '1500' => 'New',
     *     '1750' => 'New',
     *     '3000' => 'Used',
     *     '4000' => 'Used',
     *     '5000' => 'Used',
     *     '6000' => 'Used'
     * );
     *
     * @param  array  $array  The source array.
     *
     * @return  array  The inverted array.
     *
     * @since   2.0
     */
    public static function invert(array $array): array
    {
        $return = [];

        foreach ($array as $base => $values) {
            if (!is_array($values)) {
                continue;
            }

            foreach ($values as $key) {
                // If the key isn't scalar then ignore it.
                if (is_scalar($key)) {
                    $return[$key] = $base;
                }
            }
        }

        return $return;
    }

    /**
     * Method to determine if an array is an associative array.
     *
     * @param  array  $array  An array to test.
     *
     * @return  bool  True if the array is an associative array.
     *
     * @since   2.0
     */
    public static function isAssociative(array $array): bool
    {
        foreach (array_keys($array) as $k => $v) {
            if ($k !== $v) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is a value an array or array accessible.
     *
     * @param  mixed  $array
     *
     * @return  bool
     *
     * @since  4.0
     */
    public static function isAccessible(mixed $array): bool
    {
        return is_array($array) || $array instanceof ArrayAccess;
    }

    /**
     * Multidimensional array safe unique test
     *
     * @param  array  $array  The array to make unique.
     *
     * @return  array
     *
     * @see     https://www.php.net/manual/en/function.array-unique.php#97285
     * @since   2.0
     */
    public static function unique(array $array): array
    {
        $array = array_map('serialize', $array);
        $array = array_unique($array);
        $array = array_map('unserialize', $array);

        return $array;
    }

    /**
     * To get 2-dimensional array columns without native php array_column()
     *
     * @param  array|object  $src
     * @param  string        $column
     * @param  ?string       $keyName
     * @param  bool          $invasive
     *
     * @return  array
     */
    public static function getColumn(
        array|object $src,
        string $column,
        ?string $keyName = null,
        bool $invasive = true
    ): array {
        $result = [];

        foreach (TypeCast::toIterable($src) as $key => $item) {
            if (is_object($item) && !isset($item->$column) && $invasive) {
                $ref = new ReflectionObject($item);

                if (!$ref->hasProperty($column)) {
                    continue;
                }

                $prop = $ref->getProperty($column);
                $prop->setAccessible(true);
                $value = $prop->getValue($item);

                if ($keyName !== null) {
                    if ($ref->hasProperty($keyName)) {
                        $prop = $ref->getProperty($keyName);
                        $prop->setAccessible(true);
                        $keyName = $prop->getValue($item);
                    } else {
                        $keyName = null;
                    }
                }
            } else {
                if (!static::has($item, $column, '')) {
                    continue;
                }

                $value = static::get($item, $column, '');

                if ($keyName !== null) {
                    $keyName = static::get($item, $keyName, '');
                }
            }

            if ($keyName) {
                $result[$keyName] = $item;
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * flatMap
     *
     * @param  array|object  $array
     * @param  callable      $callback
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function flatMap(object|array $array, callable $callback): array
    {
        $mapped = [];

        foreach ($array as $key => $value) {
            $result = $callback($value, $key, $array);

            if (is_iterable($result)) {
                foreach ($result as $k => $v) {
                    $mapped[$k] = $v;
                }
            } elseif ($result !== null) {
                $mapped[$key] = $result;
            }
        }

        return $mapped;
    }

    /**
     * mapRecursive
     *
     * @param  array     $array
     * @param  callable  $callback
     * @param  bool      $useKeys
     * @param  bool      $loopIterable
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function mapRecursive(
        array $array,
        callable $callback,
        bool $useKeys = false,
        bool $loopIterable = false
    ): array {
        $result = [];

        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $item = static::mapRecursive($item, $callback, $useKeys, $loopIterable);
            } elseif ($loopIterable && is_iterable($item)) {
                $item = static::mapRecursive(iterator_to_array($item), $callback, $useKeys, $loopIterable);
            } else {
                $item = $callback($item, ...($useKeys ? [$key] : []));
            }

            $result[$key] = $item;
        }

        return $result;
    }

    /**
     * Merge array recursively.
     *
     * @param  array  ...$args
     *
     * @return  array Merged array.
     * @since   4.0
     */
    public static function mergeRecursive(...$args): array
    {
        $result = [];

        foreach ($args as $i => $array) {
            if (!is_array($array)) {
                throw new InvalidArgumentException(sprintf('Argument #%d is not an array.', $i + 2));
            }

            foreach ($array as $key => &$value) {
                if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = static::mergeRecursive($result [$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * arrayEquals
     *
     * @param  array  $a
     * @param  array  $b
     *
     * @return  bool
     */
    public static function arrayEquals(array $a, array $b): bool
    {
        return count($a) === count($b)
            && array_diff($a, $b) === array_diff($b, $a);
    }

    /**
     * Recursive dump variables and limit by level.
     *
     * @param  mixed  $data   The variable you want to dump.
     * @param  int    $depth  The level number to limit recursive loop.
     *
     * @return  string  Dumped data.
     *
     * @since   2.0
     */
    public static function dump(mixed $data, int $depth = 5): string
    {
        static $innerLevel = 1;
        static $tabLevel = 1;

        $self = __FUNCTION__;

        $type       = gettype($data);
        $tabs       = str_repeat('    ', $tabLevel);
        $quoteTabes = str_repeat('    ', $tabLevel - 1);
        $output     = '';
        $elements   = [];

        $recursiveType = ['object', 'array'];

        // Recursive
        if (in_array($type, $recursiveType, true)) {
            // If type is object, try to get properties by Reflection.
            if ($type === 'object') {
                // Remove special characters from anonymous class name.
                $ref        = new ReflectionObject($data);
                $class      = $ref->isAnonymous() ? 'class@anonymous' : $ref->getName();
                $output     = $class . ' ' . ucfirst($type);
                $properties = $ref->getProperties();

                // Fix for ArrayObject & ArrayIterator
                if ($data instanceof ArrayObject || $data instanceof ArrayIterator) {
                    $data->setFlags(ArrayObject::ARRAY_AS_PROPS);
                }

                foreach ($properties as $property) {
                    $property->setAccessible(true);

                    $pType = $property->getName();

                    if ($property->isProtected()) {
                        $pType .= ':protected';
                    } elseif ($property->isPrivate()) {
                        $pType .= ':' . $class . ':private';
                    }

                    if ($property->isStatic()) {
                        $pType .= ':static';
                    }

                    $elements[$pType] = $property->getValue($data);
                }

                if ($data instanceof ArrayObject || $data instanceof ArrayIterator) {
                    $data->setFlags(0);
                }
            } elseif ($type === 'array') {
                // If type is array, just return it's value.
                $output   = ucfirst($type);
                $elements = $data;
            }

            // Start dumping data
            if ($depth === 0 || $innerLevel < $depth) {
                // Start recursive print
                $output .= "\n{$quoteTabes}(";

                foreach ($elements as $key => $element) {
                    $output .= "\n{$tabs}[{$key}] => ";

                    // Increment level
                    $tabLevel += 2;
                    $innerLevel++;

                    $output .= in_array(gettype($element), $recursiveType, true)
                        ? static::$self($element, $depth)
                        : $element;

                    // Decrement level
                    $tabLevel -= 2;
                    $innerLevel--;
                }

                $output .= "\n{$quoteTabes})\n";
            } else {
                $output .= "\n{$quoteTabes}*MAX LEVEL*\n";
            }
        } else {
            $output = $data;
        }

        return (string) $output;
    }

    /**
     * Find value from array.
     *
     * @param  mixed         $array
     * @param  string|array  $field
     * @param  string|null   $operator
     * @param  mixed         $value
     * @param  bool          $strict
     * @param  bool          $keepKey
     *
     * @return  array
     *
     * @since  3.5.1
     */
    public static function where(
        mixed $array,
        array|string $field,
        ?string $operator = null,
        $value = null,
        bool $strict = false,
        bool $keepKey = false
    ): array {
        if (is_string($field)) {
            $operator = $operator === '=' ? '' : $operator;

            $query = [$field . rtrim(' ' . $operator) => $value];
        } elseif (is_array($field)) {
            $query = $field;
        } else {
            throw new InvalidArgumentException('Where query must br array or string.');
        }

        return static::query($array, $query, $strict, $keepKey);
    }

    /**
     * Query a two-dimensional array values to get second level array.
     *
     * @param  array|object  $array           An array to query.
     * @param  array         $queries         Query strings or callback, may contain Comparison Operators: '>', '>=',
     *                                        '<', '<='. Example: array(
     *                                        'id'          => 6,   // Get all elements where id=6
     *                                        'published >' => 0    // Get all elements where published>0
     *                                        );
     * @param  bool          $strict          Use strict to compare equals.
     * @param  bool          $keepKey         Keep origin array keys.
     *
     * @return  array|object  An new two-dimensional array queried.
     *
     * @since   2.0
     */
    public static function query(
        object|array $array,
        $queries = [],
        bool $strict = false,
        bool $keepKey = false
    ): object|array {
        $array   = TypeCast::toArray($array, false);
        $results = [];

        // If queries is callback, we run this logic to compare values.
        $callback = is_callable($queries) ? $queries : false;

        // Visit Array
        foreach ($array as $key => $value) {
            if ($callback) {
                $match = $callback($value, $key, $strict);
            } else {
                $match = static::match(TypeCast::toArray($value), $queries, $strict);
            }

            // Set Query results
            if ($match) {
                if ($keepKey) {
                    $results[$key] = $value;
                } else {
                    $results[] = $value;
                }
            }
        }

        return $results;
    }

    /**
     * Query a two-dimensional array values to get second level array, return only first.
     *
     * @param  array|object  $array           An array to query.
     * @param  array         $queries         Query strings or callback, may contain Comparison Operators: '>', '>=',
     *                                        '<', '<='. Example: array(
     *                                        'id'          => 6,   // Get all elements where id=6
     *                                        'published >' => 0    // Get all elements where published>0
     *                                        );
     * @param  bool          $strict          Use strict to compare equals.
     *
     * @return mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function queryFirst(object|array $array, $queries = [], bool $strict = false): mixed
    {
        $result = static::query($array, $queries, $strict, false);

        return $result[0] ?? null;
    }

    /**
     * Check an array match our query.
     *
     * @param  array  $array    An array to query.
     * @param  array  $queries  Query strings or callback, may contain Comparison Operators: '>', '>=', '<', '<='.
     * @param  bool   $strict   Use strict to compare equals.
     *
     * @return  bool
     */
    public static function match(array $array, array $queries, bool $strict = false): bool
    {
        $results = [];

        // Visit Query Rules
        foreach ($queries as $key => $val) {
            if ($val instanceof WhereWrapper) {
                $results[] = $val($array);
            } elseif (is_callable($val)) {
                $results[] = $val($array[$key], $key);
            } elseif (substr($key, -2) === '>=') {
                $results[] = (static::get($array, trim(substr($key, 0, -2))) >= $val);
            } elseif (substr($key, -2) === '<=') {
                $results[] = (static::get($array, trim(substr($key, 0, -2))) <= $val);
            } elseif (substr($key, -1) === '>') {
                $results[] = (static::get($array, trim(substr($key, 0, -1))) > $val);
            } elseif (substr($key, -1) === '<') {
                $results[] = (static::get($array, trim(substr($key, 0, -1))) < $val);
            } elseif (is_array($val)) {
                $results[] = in_array(static::get($array, $key), $val, $strict);
            } elseif ($strict) {
                $results[] = static::get($array, $key) === $val;
            } else {
                // Workaround for PHP object compare bug, see: https://bugs.php.net/bug.php?id=62976
                $compare1 = is_object(static::get($array, $key)) ? get_object_vars(
                    static::get(
                        $array,
                        $key
                    )
                ) : static::get($array, $key);
                $compare2 = is_object($val) ? get_object_vars($val) : $val;

                $results[] = ($compare1 == $compare2);
            }
        }

        // Must all TRUE.
        return !in_array(false, $results, true);
    }

    /**
     * filterRecursive
     *
     * @param  array     $array
     * @param  callable  $callback
     *
     * @return  array
     */
    public static function filterRecursive(array $array, callable $callback): array
    {
        foreach ($array as $key => & $value) { // mind the reference
            if (is_array($value)) {
                $value = static::filterRecursive($value, $callback);
            } elseif ($callback !== null && !$callback($value, $key)) {
                unset($array[$key]);
            } elseif (!(bool) $value) {
                unset($array[$key]);
            }
        }

        unset($value);

        return $array;
    }

    public static function show(...$args): void
    {
        if (is_resource(static::$output)) {
            $output = static::$output;
        } elseif (PHP_SAPI === 'cli' || defined('STDOUT')) {
            $output = STDOUT;
        } else {
            $output = fopen('php://output', 'wb');
        }

        if (VarDumper::isSupported()) {
            $dumper = [VarDumper::class, 'dump'];
        } else {
            $dumper = [static::class, 'dump'];
        }

        $level = 5;

        if (count($args) > 1) {
            $last = array_pop($args);

            if (is_int($last)) {
                $level = $last;
            } else {
                $args[] = $last;
            }
        }

        fwrite($output, "\n\n");

        if (PHP_SAPI !== 'cli') {
            fwrite($output, '<pre>');
        }

        // Dump Multiple values
        if (count($args) > 1) {
            $prints = [];

            $i = 1;

            foreach ($args as $arg) {
                $prints[] = '[Value ' . $i . "]\n" . $dumper($arg, $level);
                $i++;
            }

            fwrite($output, implode("\n\n", $prints));
        } else {
            // Dump one value.
            fwrite($output, $dumper($args[0], $level));
        }

        if (PHP_SAPI !== 'cli') {
            fwrite($output, '</pre>');
        }
    }
}
