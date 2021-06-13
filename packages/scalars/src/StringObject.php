<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Scalars;

use ArrayAccess;
use BadMethodCallException;
use Closure;
use Countable;
use IteratorAggregate;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use ReflectionParameter;
use Traversable;
use Windwalker\Scalars\Concern\StringInflectorTrait;
use Windwalker\Scalars\Concern\StringModifyTrait;
use Windwalker\Utilities\Classes\FlowControlTrait;
use Windwalker\Utilities\Classes\ImmutableHelperTrait;
use Windwalker\Utilities\Classes\MarcoableTrait;
use Windwalker\Utilities\Contract\NullableInterface;
use Windwalker\Utilities\Str;
use Windwalker\Utilities\Utf8String;

/**
 * The StringObject class.
 *
 * @see    Str
 *
 * @method StringObject getChar(int $pos)
 * @method StringObject between(string $start, string $end, int $offset = 0)
 * @method StringObject collapseWhitespaces()
 * @method bool         contains(string $search, bool $caseSensitive = true)
 * @method bool         endsWith(string $search, bool $caseSensitive = true)
 * @method bool         startsWith(string $target, bool $caseSensitive = true)
 * @method StringObject ensureLeft(string $search)
 * @method StringObject ensureRight(string $search)
 * @method bool         hasLowerCase()
 * @method bool         hasUpperCase()
 * @method StringObject match(string $pattern, string $option = 'msr')
 * @method StringObject insert(string $insert, int $position)
 * @method bool         isLowerCase()
 * @method bool         isUpperCase()
 * @method StringObject first(int $length = 1)
 * @method StringObject last(int $length = 1)
 * @method StringObject intersectLeft(string $string2)
 * @method StringObject intersectRight(string $string2)
 * @method StringObject intersect(string $string2)
 * @method StringObject pad(int $length = 0, string $substring = ' ')
 * @method StringObject padLeft(int $length = 0, string $substring = ' ')
 * @method StringObject padRight(int $length = 0, string $substring = ' ')
 * @method StringObject removeChar(int $offset, int $length = null)
 * @method StringObject removeLeft(string $search)
 * @method StringObject removeRight(string $search)
 * @method StringObject slice(int $start, int $end = null)
 * @method StringObject substring(int $start, int $end = null)
 * @method StringObject surrounds($substring = ['"', '"'])
 * @method StringObject toggleCase()
 * @method StringObject truncate(int $length, string $suffix = '', bool $wordBreak = true)
 * @method StringObject map(callable $callback)
 * @method StringObject filter(callable $callback)
 * @method StringObject reject(callable $callback)
 * @method StringObject toUpperCase()
 * @method StringObject toLowerCase()
 * @method int|bool     strpos(string $search)
 * @method int|bool     strrpos(string $search)
 * @method ArrayObject  split(string $delimiter, ?int $limit = null)
 *
 * @since  __DEPLOY_VERSION__
 */
class StringObject implements Countable, ArrayAccess, IteratorAggregate, \Stringable, NullableInterface
{
    use MarcoableTrait;
    use ImmutableHelperTrait;
    use StringModifyTrait;
    use StringInflectorTrait;
    use FlowControlTrait;

    /**
     * We only provides 3 default encoding constants of PHP.
     * @see http://php.net/manual/en/xml.encoding.php
     */
    public const ENCODING_DEFAULT_ISO = 'ISO-8859-1';

    public const ENCODING_UTF8 = 'UTF-8';

    public const ENCODING_US_ASCII = 'US-ASCII';

    /**
     * Property string.
     *
     * @var  string
     */
    protected $string = '';

    /**
     * Property encoding.
     *
     * @var  string
     */
    protected $encoding = null;

    /**
     * StringObject constructor.
     *
     * @see  http://php.net/manual/en/mbstring.supported-encodings.php
     *
     * @param  string       $string
     * @param  null|string  $encoding
     */
    public function __construct($string = '', ?string $encoding = self::ENCODING_UTF8)
    {
        $this->string   = (string) $string;
        $this->encoding = $encoding ?? static::ENCODING_UTF8;
    }

    /**
     * create
     *
     * @param  string       $string
     * @param  null|string  $encoding
     *
     * @return  static
     */
    public static function create(string $string = '', ?string $encoding = self::ENCODING_UTF8): StringObject
    {
        return new static($string, $encoding);
    }

    public static function wrap(mixed $string = '', ?string $encoding = self::ENCODING_UTF8): StringObject
    {
        if ($string instanceof static) {
            return $string;
        }

        return new static((string) $string, $encoding);
    }

    /**
     * __call
     *
     * @param  string  $name
     * @param  array   $args
     *
     * @return  mixed
     * @throws BadMethodCallException
     * @throws ReflectionException
     */
    public function __call(string $name, array $args): mixed
    {
        $class = Str::class;

        if (is_callable([$class, $name])) {
            return $this->callProxy($class, $name, $args);
        }

        $maps = [
            'toUpperCase' => [Utf8String::class, 'strtoupper'],
            'toLowerCase' => [Utf8String::class, 'strtolower'],
            'split' => [$this, 'explode'],
        ];

        if ($maps[$name] ?? null) {
            return $this->callProxy($maps[$name][0], $maps[$name][1], $args);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s::%s()', static::class, $name));
    }

    /**
     * callProxy
     *
     * @param  string  $class
     * @param  string  $method
     * @param  array   $args
     *
     * @return  mixed
     * @throws ReflectionException
     */
    protected function callProxy(string $class, string $method, array $args): mixed
    {
        $new = $this->cloneInstance();

        $closure = Closure::fromCallable([$class, $method]);

        if (method_exists($class, $method)) {
            $ref = new ReflectionMethod($class, $method);
        } else {
            $ref = (new ReflectionObject($closure))->getMethod('__invoke');
        }

        $params = $ref->getParameters();

        array_shift($params);

        /** @var ReflectionParameter $param */
        foreach (array_values($params) as $k => $param) {
            if (!array_key_exists($k, $args)) {
                if ($param->getName() === 'encoding' && !isset($args[$k])) {
                    $args[$k] = $this->encoding;
                    continue;
                }

                $args[$k] = $param->getDefaultValue();
            }
        }

        $result = $closure($new->string, ...$args);

        if (is_string($result)) {
            $new->string = $result;

            return $new;
        }

        return $result;
    }

    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *        <b>Traversable</b>
     */
    public function getIterator(): Traversable
    {
        return $this->chop()->values();
    }

    /**
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param  mixed  $offset  <p>
     *                         An offset to check for.
     *                         </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists(mixed $offset): bool
    {
        $offset = $offset >= 0 ? $offset : (int) abs($offset) - 1;

        return $this->length() > $offset;
    }

    /**
     * length
     *
     * @return  int
     */
    public function length(): int
    {
        return Utf8String::strlen($this->string, $this->encoding);
    }

    /**
     * Offset to retrieve
     *
     * @param  int  $offset  The offset to retrieve.
     *
     * @return string|static Can return all value types.
     */
    public function offsetGet(mixed $offset): StringObject|string|static
    {
        return $this->getChar($offset);
    }

    /**
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param  mixed  $offset  The offset to assign the value to.
     * @param  mixed  $string  The value to set.
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $string): void
    {
        $this->string = Utf8String::substrReplace($this->string, $string, $offset, 1, $this->encoding);
    }

    /**
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param  mixed  $offset  The offset to unset.
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        if ($this->length() < abs($offset)) {
            return;
        }

        $this->string = Str::removeChar($this->string, $offset, 1, $this->encoding);
    }

    /**
     * Count elements of an object
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *             The return value is cast to an integer.
     */
    public function count(): int
    {
        return $this->length();
    }

    /**
     * Magic method to convert this object to string.
     *
     * @return  string
     */
    public function __toString(): string
    {
        return (string) $this->string;
    }

    public function toInteger(): int
    {
        return (int) $this->string;
    }

    public function toFloat(): float
    {
        return (float) $this->string;
    }

    /**
     * Method to get property Encoding
     *
     * @return  string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * Method to set property encoding
     *
     * @param  string  $encoding
     *
     * @return  static  Return self to support chaining.
     */
    public function withEncoding(string $encoding): static
    {
        return $this->cloneInstance(
            static function (StringObject $new) use ($encoding) {
                $new->encoding = $encoding;
            }
        );
    }

    /**
     * Method to get property String
     *
     * @return  string
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * Method to set property string
     *
     * @param  string  $string
     *
     * @return  static  Return self to support chaining.
     */
    public function withString(string $string): static
    {
        return $this->cloneInstance(
            static function (StringObject $new) use ($string) {
                $new->string = $string;
            }
        );
    }

    /**
     * substrCount
     *
     * @param  string  $search
     * @param  bool    $caseSensitive
     *
     * @return  int
     */
    public function substrCount(string $search, bool $caseSensitive = true): int
    {
        return Utf8String::substrCount($this->string, $search, $caseSensitive, $this->encoding);
    }

    /**
     * explode
     *
     * @param  string    $delimiter
     * @param  int|null  $limit
     *
     * @return  ArrayObject
     */
    public function explode(string $delimiter, ?int $limit = null): ArrayObject
    {
        $limit = $limit ?? PHP_INT_MAX;

        return ArrayObject::explode($delimiter, $this->string, $limit);
    }

    /**
     * indexOf
     *
     * @param  string  $search
     *
     * @return  int
     */
    public function indexOf(string $search): int
    {
        $result = Utf8String::strpos($this->string, $search, 0, $this->encoding);

        if ($result === false) {
            return -1;
        }

        return $result;
    }

    /**
     * indexOf
     *
     * @param  string  $search
     *
     * @return  int
     */
    public function indexOfLast(string $search): int
    {
        $result = Utf8String::strrpos($this->string, $search, 0, $this->encoding);

        if ($result === false) {
            return -1;
        }

        return $result;
    }

    /**
     * apply
     *
     * @param  callable  $callback
     * @param  array     $args
     *
     * @return  static
     */
    public function apply(callable $callback, ...$args): static
    {
        return $this->cloneInstance(
            static function ($new) use ($callback, $args) {
                return $new->string = $callback($new->string, ...$args);
            }
        );
    }

    /**
     * isNull
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isNull(): bool
    {
        return (string) $this->string === '';
    }

    /**
     * notNull
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function notNull(): bool
    {
        return (string) $this->string !== '';
    }
}
