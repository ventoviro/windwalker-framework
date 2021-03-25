<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Reflection;

use Windwalker\Utilities\Str;
use Windwalker\Utilities\Utf8String;

/**
 * The BacktraceHelper class.
 */
class BacktraceHelper
{
    public static function findCalled(string $class): ?array
    {
        $inClass = false;

        foreach ($traces = debug_backtrace() as $i => $trace) {
            if ($inClass === false && ($trace['object'] ?? null) instanceof $class) {
                $inClass = true;
                continue;
            }

            if ($inClass === true && !($trace['object'] ?? null) instanceof $class) {
                return [
                    'file' => $traces[$i - 2]['file'] ?? '',
                    'line' => $traces[$i - 2]['line'] ?? '',
                    'class' => $traces[$i - 1]['class'] ?? '',
                    'function' => $traces[$i - 1]['function'] ?? '',
                ];
            }
        }

        return null;
    }

    public static function findCaller(string $class): ?array
    {
        $inClass = false;

        foreach ($traces = debug_backtrace() as $i => $trace) {
            if ($inClass === false && ($trace['object'] ?? null) instanceof $class) {
                $inClass = true;
                continue;
            }

            if ($inClass === true && !($trace['object'] ?? null) instanceof $class) {
                return [
                    'file' => $traces[$i - 1]['file'] ?? '',
                    'line' => $traces[$i - 1]['line'] ?? '',
                    'class' => $traces[$i]['class'] ?? '',
                    'function' => $traces[$i]['function'] ?? '',
                ];
            }
        }

        return null;
    }

    /**
     * normalizeBacktrace
     *
     * @param  array        $trace
     * @param  string|null  $replaceRoot
     *
     * @return  array
     * @throws \ReflectionException
     */
    public static function normalizeBacktrace(array $trace, ?string $replaceRoot = null): array
    {
        $args = [];

        $trace['class'] ??= null;
        $trace['args']  ??= [];
        $trace['file']  ??= null;
        $trace['line']  ??= null;

        foreach ((array) $trace['args'] as $arg) {
            if (is_array($arg)) {
                $arg = 'Array';
            } elseif (is_object($arg)) {
                $arg = (new \ReflectionClass($arg))->getShortName();
            } elseif (is_string($arg)) {
                if (Utf8String::strlen($arg) > 20) {
                    $arg = Utf8String::substr($arg, 0, 20) . '...';
                }

                $arg = Str::wrap($arg);
            } elseif ($arg === null) {
                $arg = 'NULL';
            } elseif (is_bool($arg)) {
                $arg = $arg ? 'TRUE' : 'FALSE';
            }

            $args[] = $arg;
        }

        $file = $trace['file'] ?? '';

        if ($file) {
            $file = $replaceRoot ? static::replaceRoot($file, $replaceRoot) : $file;
            $file .= ':' . $trace['line'];
        }

        return [
            'file' => $file,
            'function' => ($trace['class'] ? $trace['class'] . $trace['type'] : null) . $trace['function'] .
                sprintf('(%s)', implode(', ', $args)),
            'pathname' => $trace['file'],
            'line' => $trace['line'],
        ];
    }

    /**
     * normalizeBacktraces
     *
     * @param  array  $traces
     * @param  bool   $replaceRoot
     *
     * @return  array
     */
    public static function normalizeBacktraces(array $traces, ?string $replaceRoot = null): array
    {
        $return = [];

        foreach ($traces as $trace) {
            $return[] = $trace ? static::normalizeBacktrace($trace, $replaceRoot) : null;
        }

        return $return;
    }

    /**
     * traceAsString
     *
     * @param  int    $i
     * @param  array  $trace
     * @param  bool   $replaceRoot
     *
     * @return  string
     *
     * @since  3.5.7
     */
    public static function traceAsString(int $i, array $trace, ?string $replaceRoot = null): string
    {
        $frameData = static::normalizeBacktrace($trace, $replaceRoot);

        return sprintf(
            '%3d. %s %s',
            $i,
            $frameData['function'],
            $frameData['file']
        );
    }

    /**
     * replaceRoot
     *
     * @param  string       $file
     * @param  string|null  $replaceRoot
     *
     * @return  string
     */
    public static function replaceRoot(string $file, ?string $replaceRoot = null): string
    {
        if ($replaceRoot) {
            $file = 'ROOT' . substr($file, strlen(WINDWALKER_ROOT));
        }

        return $file;
    }
}
