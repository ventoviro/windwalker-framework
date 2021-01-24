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
 * The PhpSerializeFormat class.
 */
class PhpSerializeFormat implements FormatInterface
{
    /**
     * dump
     *
     * @param  array|object  $data
     * @param  array         $options
     *
     * @return  string
     */
    public function dump(mixed $data, array $options = []): string
    {
        return serialize($data);
    }

    /**
     * parse
     *
     * @param  string  $string
     * @param  array   $options
     *
     * @return  array
     */
    public function parse(string $string, array $options = []): array
    {
        return unserialize($string, $options);
    }
}
