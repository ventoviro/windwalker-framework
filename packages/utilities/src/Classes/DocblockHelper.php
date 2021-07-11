<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

use ReflectionClass;
use ReflectionMethod;
use ReflectionType;
use ReflectionUnionType;
use Windwalker\Utilities\Str;

/**
 * The DocblockHelper class.
 *
 * @since  3.0
 */
class DocblockHelper
{
    /**
     * listVarTypes
     *
     * @param  array  $data
     *
     * @return  string
     */
    public static function listVarTypes(array $data): string
    {
        $vars = [];

        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $type = '\\' . get_class($value);
            } else {
                $type = gettype($value);
            }

            $vars[] = sprintf(' * @var  $%s  %s', $key, $type);
        }

        return static::renderDocblock(implode("\n", $vars));
    }

    /**
     * listMethods
     *
     * @param  mixed  $class
     * @param  int    $flags
     *
     * @return  string
     */
    public static function listMethods(
        mixed $class,
        int $flags = ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC
    ): string {
        $ref = new ReflectionClass($class);

        $methods = $ref->getMethods($flags);

        $lines = [];

        /** @var ReflectionMethod $method */
        foreach ($methods as $method) {
            $type = $method->getReturnType();

            if (!$type) {
                preg_match('/\s+\*\s+@return\s+([\w]+)\s*[\w ]*/', (string) $method->getDocComment(), $matches);

                $return = $matches[1] ?? 'void';

                if ($return === 'static' || $return === 'self' || $return === '$this') {
                    $return = $method->getDeclaringClass()->getName();
                }

                if (class_exists($return)) {
                    $return = '\\' . $return;
                }
            } else {
                $return = self::typeToString($type);
            }

            $source = file($method->getFileName());
            $body = implode(
                "",
                array_slice(
                    $source,
                    $method->getStartLine() - 1,
                    $method->getEndLine() - $method->getStartLine()
                )
            );

            preg_match('/\s+public\s+[static]*\s*function\s+(\w+)\((.*?)\)/ms', $body, $matches);

            if (!$matches) {
                continue;
            }

            $func = $matches[1];
            $body = $matches[2];
            $body = trim(Str::collapseWhitespaces(str_replace("\n", ' ', $body)));

            $lines[] = sprintf(' * @method  %s  %s(%s)', $return, $func, $body);
        }

        return static::renderDocblock(implode("\n", $lines));
    }

    public static function typeToString(ReflectionType $type): string
    {
        if ($type instanceof ReflectionUnionType) {
            $types = $type->getTypes();
        } else {
            $types = [$type];
        }

        $types = array_map(
            function (ReflectionType $type) {
                $name = $type->getName();

                if (class_exists($name) || interface_exists($name)) {
                    $name = '\\' . trim($name, '\\');
                }

                return $name;
            },
            $types
        );

        if ($type->allowsNull()) {
            $types[] = 'null';
        }

        return implode('|', $types);
    }

    /**
     * renderDocblock
     *
     * @param  string  $content
     *
     * @return  string
     */
    public static function renderDocblock(string $content): string
    {
        $tmpl = <<<TMPL
/**
%s
 */
TMPL;

        return sprintf($tmpl, $content);
    }
}
