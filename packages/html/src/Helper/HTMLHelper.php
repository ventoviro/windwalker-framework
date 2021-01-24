<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Html\Helper;

use JsonException;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * The HtmlHelper class.
 *
 * @since  2.0
 */
abstract class HTMLHelper
{
    /**
     * Repair HTML. If Tidy not exists, use repair function.
     *
     * @param  string  $html     The HTML string to repair.
     * @param  bool    $useTidy  Force tidy or not.
     *
     * @return  string  Repaired HTML.
     */
    public static function repair(string $html, bool $useTidy = true): string
    {
        if (function_exists('tidy_repair_string') && $useTidy) {
            $TidyConfig = [
                'indent' => true,
                'output-xhtml' => true,
                'show-body-only' => true,
                'wrap' => false,
            ];

            return tidy_repair_string($html, $TidyConfig, 'utf8');
        }

        $arr_single_tags = ['meta', 'img', 'br', 'link', 'area'];

        // Put all opened tags into an array
        preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $html, $result);
        $openedtags = $result[1];

        // Put all closed tags into an array
        preg_match_all("#</([a-z]+)>#iU", $html, $result);

        $closedtags = $result[1];
        $len_opened = count($openedtags);

        // All tags are closed
        if (count($closedtags) === $len_opened) {
            return $html;
        }

        $openedtags = array_reverse($openedtags);

        // Close tags
        for ($i = 0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags, true)) {
                if (!in_array($openedtags[$i], $arr_single_tags, true)) {
                    $html .= "</" . $openedtags[$i] . ">";
                }
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags, true)]);
            }
        }

        return $html;
    }

    /**
     * Method to get a JavaScript object notation string from an array
     *
     * @param  mixed  $data      The data to convert to JavaScript object notation
     * @param  bool   $quoteKey  Quote json key or not.
     *
     * @return string JavaScript object notation representation of the array
     *
     * @throws JsonException
     * @since  2.0
     */
    public static function getJSObject($data = [], bool $quoteKey = false): string
    {
        if ($data === null) {
            return 'null';
        }

        if ($data instanceof RawWrapper) {
            return (string) $data->get();
        }

        $output = '';

        switch (gettype($data)) {
            case 'boolean':
                $output .= $data ? 'true' : 'false';
                break;

            case 'float':
            case 'double':
            case 'integer':
                $output .= $data + 0;
                break;

            case 'array':
                if (!Arr::isAssociative($data)) {
                    $child = [];

                    foreach ($data as $value) {
                        $child[] = static::getJSObject($value, $quoteKey);
                    }

                    $output .= '[' . implode(',', $child) . ']';
                    break;
                }
            // Go to next body

            case 'object':
                $array = is_object($data) ? get_object_vars($data) : $data;

                $row = [];

                foreach ($array as $key => $value) {
                    $key = json_encode($key, JSON_THROW_ON_ERROR);

                    if (!$quoteKey) {
                        $key = substr(substr($key, 0, -1), 1);
                    }

                    $row[] = $key . ':' . static::getJSObject($value, $quoteKey);
                }

                $output .= '{' . implode(',', $row) . '}';
                break;

            default:
                // anything else is treated as a string
                return json_encode($data, JSON_THROW_ON_ERROR);
        }

        return $output;
    }
}
