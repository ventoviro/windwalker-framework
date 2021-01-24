<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Data\Format;

use Traversable;
use Windwalker\Utilities\Arr;
use Yosymfony\Toml\Toml;
use Yosymfony\Toml\TomlBuilder;

/**
 * The TomlFormat class.
 *
 * @since  3.5.4
 */
class TomlFormat implements FormatInterface
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
        $tb = new TomlBuilder();

        if ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        } else {
            $data = (array) $data;
        }

        static::addValues($tb, $data);

        return $tb->getTomlString();
    }

    /**
     * addValues
     *
     * @param  TomlBuilder  $tb
     * @param  mixed        $struct
     * @param  string|null  $prefix
     *
     * @return  void
     *
     * @since  3.5.4
     */
    protected static function addValues(TomlBuilder $tb, mixed $struct, ?string $prefix = null): void
    {
        foreach ($struct as $key => $value) {
            if (is_array($value) && Arr::isAssociative($value)) {
                $tb->addTable($key = trim($prefix . '.' . $key, '.'));

                static::addValues($tb, $value, $key);
            } else {
                $tb->addValue($key, $value);
            }
        }
    }

    /**
     * Converts a formatted string into an object.
     *
     * @param  string  $string   Formatted string
     * @param  array   $options  An array of options for the formatter.
     *
     * @return mixed|\stdClass|null Data Object
     *
     * @since   2.0
     */
    public function parse(string $string, array $options = []): mixed
    {
        return Toml::parse($string, (bool) ($options['resultAsObject'] ?? false));
    }
}
