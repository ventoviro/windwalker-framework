<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Data\Format;

use Symfony\Component\Yaml\Yaml;

/**
 * YAML format handler for Data.
 *
 * @since  2.0
 */
class YamlFormat implements FormatInterface
{
    /**
     * Converts an object into a YAML formatted string.
     * We use json_* to convert the passed object to an array.
     *
     * @param  object  $data     Data source object.
     * @param  array   $options  Options used by the formatter.
     *
     * @return  string  YAML formatted string.
     *
     * @since   2.0
     */
    public function dump(mixed $data, array $options = []): string
    {
        $inline = $options['inline'] ?? 2;
        $indent = $options['indent'] ?? 0;
        $flags  = $options['flags'] ?? 0;

        return Yaml::dump($data, $inline, $indent, $flags);
    }

    /**
     * Parse a YAML formatted string and convert it into an object.
     * We use the json_* methods to convert the parsed YAML array to an object.
     *
     * @param  string  $string   YAML formatted string to convert.
     * @param  array   $options  Options used by the formatter.
     *
     * @return mixed Data array.
     *
     * @since   2.0
     */
    public function parse(string $string, array $options = []): mixed
    {
        $flags = $options['flags'] ?? 0;

        return Yaml::parse(trim($string), $flags);
    }
}
