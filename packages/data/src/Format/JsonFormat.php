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
 * JSON format handler for Data.
 *
 * @since  2.0
 */
class JsonFormat implements FormatInterface
{
    /**
     * Converts an object into a JSON formatted string.
     *
     * @param  object  $data     Data source object.
     * @param  array   $options  Options used by the formatter.
     *
     * @return  string
     */
    public function dump(mixed $data, array $options = []): string
    {
        $depth = $options['depth'] ?? 512;
        $option = $options['options'] ?? 0;

        return json_encode($data, JSON_THROW_ON_ERROR | $option, $depth);
    }

    /**
     * Parse a JSON formatted string and convert it into an object.
     *
     * @param  string  $string   JSON formatted string to convert.
     * @param  array   $options  Options used by the formatter.
     *
     * @return mixed Data array.
     */
    public function parse(string $string, array $options = []): mixed
    {
        $assoc = $options['assoc'] ?? true;
        $depth = $options['depth'] ?? 512;
        $option = $options['options'] ?? 0;

        return json_decode(trim($string), $assoc, $depth, $option);
    }
}
