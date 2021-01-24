<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DOM\Format;

use InvalidArgumentException;
use RuntimeException;
use Windwalker\DOM\DOMFactory;

/**
 * The AbstractFormatter class.
 *
 *  This class based on https://github.com/gajus/dindent to help use test Dom code.
 *
 * @since  2.1
 */
class DOMFormatter
{
    /**
     * Property log.
     *
     * @var  array
     */
    protected $log = [];

    /**
     * Property options.
     *
     * @var  array
     */
    protected $options = [
        'indentation_character' => '    ',
    ];

    public const ELEMENT_TYPE_BLOCK = 0;

    public const ELEMENT_TYPE_INLINE = 1;

    public const MATCH_INDENT_NO = 0;

    public const MATCH_INDENT_DECREASE = 1;

    public const MATCH_INDENT_INCREASE = 2;

    public const MATCH_DISCARD = 3;

    /**
     * Property instance.
     *
     * @var  static
     */
    protected static $instance;

    /**
     * getInstance
     *
     * @return  static
     */
    public static function getInstance(): static
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Method to set property instance
     *
     * @param  static  $instance
     *
     * @return  void
     */
    public static function setInstance(self $instance)
    {
        static::$instance = $instance;
    }

    /**
     * format
     *
     * @param  string  $buffer
     *
     * @return  string  Formatted Html string.
     */
    public static function format(string $buffer): string
    {
        return static::getInstance()->indent($buffer);
    }

    /**
     * Constructor.
     *
     * @param  array  $options
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $name => $value) {
            if (!array_key_exists($name, $this->options)) {
                throw new InvalidArgumentException('Unrecognized option.');
            }

            $this->options[$name] = $value;
        }
    }

    /**
     * indent
     *
     * @param  string  $input
     *
     * @return  string
     */
    public function indent(string $input): string
    {
        $this->log = [];

        $input = $this->removeDoubleWhiteSpace($input);

        $output = $this->doIndent($input);

        return trim($output);
    }

    /**
     * Format Dom.
     *
     * @param  string  $input  Dom input.
     *
     * @return string Indented Dom.
     */
    public function doIndent(string $input): string
    {
        $subject = $input;

        $output = '';

        $nextLineIndentationLevel = 0;

        do {
            $indentationLevel = $nextLineIndentationLevel;

            $patterns = $this->getTagPatterns();

            $rules = ['NO', 'DECREASE', 'INCREASE', 'DISCARD'];

            $match = [];

            foreach ($patterns as $pattern => $rule) {
                if ($match = preg_match($pattern, $subject, $matches)) {
                    $this->log[] = [
                        'rule' => $rules[$rule],
                        'pattern' => $pattern,
                        'subject' => $subject,
                        'match' => $matches[0],
                    ];

                    $subject = mb_substr($subject, mb_strlen($matches[0]));

                    if ($rule === static::MATCH_DISCARD) {
                        break;
                    }

                    if ($rule === static::MATCH_INDENT_NO) {
                        //
                    } else {
                        if ($rule === static::MATCH_INDENT_DECREASE) {
                            $nextLineIndentationLevel--;
                            $indentationLevel--;
                        } else {
                            $nextLineIndentationLevel++;
                        }
                    }

                    if ($indentationLevel < 0) {
                        $indentationLevel = 0;
                    }

                    $output .= str_repeat(
                        $this->options['indentation_character'],
                        $indentationLevel
                    );
                    $output .= $matches[0] . "\n";

                    break;
                }
            }
        } while ($match);

        $interpretedInput = '';

        foreach ($this->log as $e) {
            $interpretedInput .= $e['match'];
        }

        if ($interpretedInput !== $input) {
            throw new RuntimeException('Did not reproduce the exact input.');
        }

        $output = preg_replace('/(<(\w+)[^>]*>)\s*(<\/\2>)/', '\\1\\3', $output);

        return $output;
    }

    /**
     * removeDoubleWhiteSpace
     *
     * @param  string  $input
     *
     * @return  string
     */
    protected function removeDoubleWhiteSpace(string $input): string
    {
        /*
         * Removing double whitespaces to make the source code easier to read.
         * With exception of <pre>/ CSS white-space changing the default behaviour,
         * double whitespace is meaningless in HTML output.
         *
         * This reason alone is sufficient not to use Dindent in production.
         */
        $input = str_replace("\t", '', $input);
        $input = preg_replace('/\s{2,}/', ' ', $input);

        return $input;
    }

    /**
     * getTagPatterns
     *
     * @return  array
     */
    protected function getTagPatterns(): array
    {
        return [
            // block tag
            '/^(<([a-z]+)(?:[^>]*)>(?:[^<]*)<\/(?:\2)>)/' => static::MATCH_INDENT_NO,
            // DOCTYPE
            '/^<!([^>]*)>/' => static::MATCH_INDENT_NO,
            // opening tag
            '/^<[^\/]([^>]*)>/' => static::MATCH_INDENT_INCREASE,
            // closing tag
            '/^<\/([^>]*)>/' => static::MATCH_INDENT_DECREASE,
            // self-closing tag
            '/^<(.+)\/>/' => static::MATCH_INDENT_DECREASE,
            // whitespace
            '/^(\s+)/' => static::MATCH_DISCARD,
            // text node
            '/([^<]+)/' => static::MATCH_INDENT_NO,
        ];
    }

    /**
     * A simple method to minify Dom and Html.
     *
     * Code from: http://stackoverflow.com/questions/6225351/how-to-minify-php-page-html-output
     *
     * @param  string  $buffer
     *
     * @return  mixed
     */
    public static function minify(string $buffer): string
    {
        $search = [
            // Strip whitespaces after tags, except space
            '/\>[^\S ]+/s',

            // Strip whitespaces before tags, except space
            '/[^\S ]+\</s',

            // Shorten multiple whitespace sequences
            '/(\s)+/s',
        ];

        $replace = [
            '>',
            '<',
            '\\1',
        ];

        $buffer = preg_replace($search, $replace, $buffer);

        $buffer = str_replace([' <', '> '], ['<', '>'], $buffer);

        return trim($buffer);
    }

    /**
     * Debugging utility. Get log for the last indent operation.
     *
     * @return array
     */
    public function getLog(): array
    {
        return $this->log;
    }

    /**
     * formatByDOMDocument
     *
     * @param  string  $string
     *
     * @return  string
     */
    public static function formatByDOMDocument(string $string): string
    {
        $dom = DOMFactory::create();
        $dom->loadXML($string);
        $dom->formatOutput = true;

        return $dom->saveXML($dom->documentElement);
    }
}
