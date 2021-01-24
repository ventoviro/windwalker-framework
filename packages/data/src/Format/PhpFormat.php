<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Data\Format;

/**
 * PHP class format handler for Data
 *
 * @since  2.0
 */
class PhpFormat implements FormatInterface
{
    /**
     * Converts an object into a php class string.
     * - NOTE: Only one depth level is supported.
     *
     * @param  array  $data     Data Source Object
     * @param  array  $options  Parameters used by the formatter
     *
     * @return  string
     */
    public function dump(mixed $data, array $options = []): string
    {
        $header = $options['header'] ?? '';
        $return = $options['return'] ?? false;
        $strict = $options['strict'] ?? true;

        // Build the object variables string
        $vars = static::getArrayString((array) $data);

        $str = '';

        if ($return) {
            if ($strict) {
                $str = "<?php\n\ndeclare(strict_types=1);\n";
            } else {
                $str = "<?php\n";
            }

            if ($header) {
                $str .= $header . "\n";
            }

            $str .= "\nreturn ";
        }

        $str .= $vars;

        if ($return) {
            $str .= ";\n";

            // Use the closing tag if set to true in parameters.
            if ($options['closingtag'] ?? false) {
                $str .= "\n?>";
            }
        }

        return $str;
    }

    /**
     * Parse a PHP class formatted string and convert it into an object.
     *
     * @param  string  $string   PHP Class formatted string to convert.
     * @param  array   $options  Options used by the formatter.
     *
     * @return void Data array.
     */
    public function parse(string $string, array $options = []): mixed
    {
        throw new \LogicException('Currently does not support parse php array.');
    }

    /**
     * Method to get an array as an exported string.
     *
     * @param  array  $a  The array to get as a string.
     * @param  int    $level
     *
     * @return  string
     */
    protected static function getArrayString(array $a, int $level = 1): string
    {
        $s = "[\n";
        $i = 0;

        $assoc = static::isAssociative($a);

        foreach ($a as $k => $v) {
            $s .= $i ? ",\n" : '';
            $s .= str_repeat('    ', $level);

            if ($assoc) {
                $s .= "'" . $k . "' => ";
            }

            if (is_array($v) || is_object($v)) {
                $s .= static::getArrayString((array) $v, $level + 1);
            } elseif ($v === null) {
                $s .= 'null';
            } elseif (is_bool($v)) {
                $s .= $v ? 'true' : 'false';
            } elseif (is_int($v) || is_float($v)) {
                $s .= $v;
            } else {
                $s .= "'" . addslashes((string) $v) . "'";
            }

            $i++;
        }

        $s .= "\n" . str_repeat('    ', $level - 1) . ']';

        return $s;
    }

    /**
     * isAssociative
     *
     * @param  array  $array
     *
     * @return  bool
     */
    private static function isAssociative(array $array): bool
    {
        foreach (array_keys($array) as $k => $v) {
            if ($k !== $v) {
                return true;
            }
        }

        return false;
    }
}
