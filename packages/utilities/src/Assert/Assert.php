<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Assert;

use Throwable;
use Windwalker\Utilities\SimpleTemplate;
use Windwalker\Utilities\Str;

/**
 * The Assert class.
 */
class Assert
{
    public string $caller;

    /**
     * @var callable
     */
    protected $exceptionHandler;

    /**
     * Assert constructor.
     *
     * @param  callable  $exceptionHandler
     * @param  ?string   $caller
     */
    public function __construct(callable $exceptionHandler, ?string $caller = null)
    {
        $this->caller           = $caller ?? static::getCaller(2);
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * assert
     *
     * @param  bool|callable  $assertion
     * @param  string         $message
     * @param  mixed          $value
     *
     * @return  void
     *
     * @throws Throwable
     *
     * @since  __DEPLOY_VERSION__
     */
    public function assert(bool|callable $assertion, string $message, $value = null): void
    {
        if (is_callable($assertion)) {
            $result = $assertion();
        } else {
            $result = (bool) $assertion;
        }

        if (!$result) {
            $this->throwException($message, $value);
        }
    }

    public function throwException(string $message, $value = null): void
    {
        throw $this->exception($message, $value);
    }

    public function exception(string $message, $value = null)
    {
        return ($this->exceptionHandler)($this->createMessage($message, $value));
    }

    protected function createMessage(string $message, $value): string
    {
        $described = static::describeValue($value);

        if (str_contains($message, '%')) {
            $message = sprintf($message, $described);
        }

        if (str_contains($message, '{')) {
            $message = SimpleTemplate::render(
                $message,
                [
                    'caller' => $this->caller,
                    'value' => $value,
                ],
                '.',
                ['{', '}']
            );
        }

        return $message;
    }

    public static function getCaller(int $backSteps = 2): string
    {
        $trace = debug_backtrace()[$backSteps];

        return trim(($trace['class'] ?? '') . '::' . ($trace['function']), ':') . '()';
    }

    public static function describeValue(mixed $value, ?int $truncate = 50): string
    {
        if ($value === null) {
            return '(NULL)';
        }

        if ($value === true) {
            return 'BOOL (TRUE)';
        }

        if ($value === false) {
            return 'BOOL (FALSE)';
        }

        if ($value instanceof \Closure) {
            $ref = new \ReflectionFunction($value);
            $classRef = $ref->getClosureScopeClass();
            $line = $ref->getStartLine() . ':' . $ref->getEndLine();

            return $classRef->getName() . "::{Closure} ($line)";
        }

        if (is_object($value)) {
            return get_class($value);
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_string($value)) {
            $value = Str::truncate($value, $truncate, '...');

            return sprintf('string(%s) "%s"', strlen($value), $value);
        }

        if (is_numeric($value)) {
            return sprintf('%s(%s)', gettype($value), $value);
        }

        return get_debug_type($value);
    }
}
