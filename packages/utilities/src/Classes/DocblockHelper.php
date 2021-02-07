<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

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
     * @param  int    $type
     *
     * @return  string
     */
    public static function listMethods(
        mixed $class,
        int $type = \ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC
    ): string {
        $ref = new \ReflectionClass($class);

        $methods = $ref->getMethods($type);

        $lines = [];

        /** @var \ReflectionMethod $method */
        foreach ($methods as $method) {
            preg_match('/\s+\*\s+@return\s+([\w]+)\s*[\w ]*/', (string) $method->getDocComment(), $matches);

            $return = $matches[1] ?? 'void';

            if ($return === 'static' || $return === 'self' || $return === '$this') {
                $return = $method->getDeclaringClass()->getName();
            }

            if (class_exists($return)) {
                $return = '\\' . $return;
            }

            $source = file($method->getFileName());
            $body   = implode("", array_slice($source, $method->getStartLine() - 1, 1));

            preg_match('/\s+public\s+[static]*\s*function\s+(.*)/', $body, $matches);
            $body = $matches[1];

            $lines[] = sprintf(' * @method  %s  %s', $return, $body);
        }

        return static::renderDocblock(implode("\n", $lines));
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
