<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Data\Format;

use HJSON\HJSONParser;
use HJSON\HJSONStringifier;

/**
 * The HjsonFormat class.
 *
 * @since  3.5.4
 */
class HjsonFormat implements FormatInterface
{
    /**
     * Converts an object into a formatted string.
     *
     * @param  object  $data     Data Source Object.
     * @param  array   $options  An array of options for the formatter.
     *
     * @return  string  Formatted string.
     *
     * @since   2.0
     */
    public function dump(mixed $data, array $options = []): string
    {
        return (new HJSONStringifier())->stringify($data, $options);
    }

    /**
     * Converts a formatted string into an object.
     *
     * @param  string  $string   Formatted string
     * @param  array   $options  An array of options for the formatter.
     *
     * @return mixed Data Object
     *
     * @since   2.0
     */
    public function parse(string $string, array $options = []): mixed
    {
        $options['assoc'] = true;

        return (new HJSONParser())->parse($string, $options);
    }
}
