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
use Windwalker\DOM\HTMLFactory;

/**
 * The HtmlFormatter class.
 *
 * @since  2.1
 */
class HTMLFormatter extends DOMFormatter
{
    /**
     * Property inlineElements.
     *
     * @var  array
     */
    protected $inlineElements = [
        'b',
        'big',
        'i',
        'small',
        'tt',
        'abbr',
        'acronym',
        'cite',
        'code',
        'dfn',
        'em',
        'kbd',
        'strong',
        'samp',
        'var',
        'a',
        'bdo',
        'br',
        'img',
        'span',
        'sub',
        'sup',
    ];

    /**
     * Property unpairedElements.
     *
     * @var  array
     */
    protected $unpairedElements = [
        'img',
        'br',
        'hr',
        'area',
        'param',
        'wbr',
        'base',
        'link',
        'meta',
        'input',
        'option',
        'a',
        'source',
    ];

    /**
     * Property temporaryReplacementsScript.
     *
     * @var  array
     */
    protected $temporaryReplacementsScript = [];

    /**
     * Property temporaryReplacementsInline.
     *
     * @var  array
     */
    protected $temporaryReplacementsInline = [];

    /**
     * indent
     *
     * @param string $input
     *
     * @return  string
     */
    public function indent(string $input): string
    {
        $this->log = [];

        $input = $this->tempScripts($input);

        $input = $this->removeDoubleWhiteSpace($input);

        $input = $this->tempInlineElements($input);

        $output = $this->doIndent($input);

        $output = $this->restoreScripts($output);

        $output = $this->restoreInlineElements($output);

        return trim($output);
    }

    /**
     * tempScripts
     *
     * @param string $input
     *
     * @return  string
     */
    protected function tempScripts(string $input): string
    {
        // Dindent does not indent <script> body. Instead, it temporary removes it from the code,
        // indents the input, and restores the script body.
        if (preg_match_all('/<script\b[^>]*>([\s\S]*?)<\/script>/mi', $input, $matches)) {
            $this->temporaryReplacementsScript = $matches[0];

            foreach ($matches[0] as $i => $match) {
                $input = str_replace($match, '<script>' . ($i + 1) . '</script>', $input);
            }
        }

        return $input;
    }

    /**
     * restoreScripts
     *
     * @param string $output
     *
     * @return  string
     */
    protected function restoreScripts(string $output): string
    {
        foreach ($this->temporaryReplacementsScript as $i => $original) {
            $output = str_replace('<script>' . ($i + 1) . '</script>', $original, $output);
        }

        return $output;
    }

    /**
     * tempInlineElements
     *
     * @param string $input
     *
     * @return  string
     */
    protected function tempInlineElements(string $input): string
    {
        // Remove inline elements and replace them with text entities.
        if (preg_match_all('/<(' . implode('|', $this->inlineElements) . ')[^>]*>(?:[^<]*)<\/\1>/', $input, $matches)) {
            $this->temporaryReplacementsInline = $matches[0];

            foreach ($matches[0] as $i => $match) {
                $input = str_replace($match, 'ᐃ' . ($i + 1) . 'ᐃ', $input);
            }
        }

        return $input;
    }

    /**
     * restoreInlineElements
     *
     * @param string $output
     *
     * @return  string
     */
    protected function restoreInlineElements(string $output): string
    {
        foreach ($this->temporaryReplacementsInline as $i => $original) {
            $output = str_replace('ᐃ' . ($i + 1) . 'ᐃ', $original, $output);
        }

        return $output;
    }

    /**
     * Method to set property inlineElements
     *
     * @param array $inlineElements
     *
     * @return  static  Return self to support chaining.
     */
    public function setInlineElements(array $inlineElements): static
    {
        $this->inlineElements = (array) $inlineElements;

        return $this;
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
            // tag with implied closing
            '/^<(' . $this->getUnpairedElements(true) . ')([^>]*)>/' => static::MATCH_INDENT_NO,
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
     * @param string  $elementName Element name, e.g. "b".
     * @param integer $type
     *
     * @return  void
     */
    public function setElementType(string $elementName, int $type): void
    {
        if ($type === static::ELEMENT_TYPE_BLOCK) {
            $this->inlineElements = array_diff($this->inlineElements, [$elementName]);
        } else {
            if ($type === static::ELEMENT_TYPE_INLINE) {
                $this->inlineElements[] = $elementName;
            } else {
                throw new InvalidArgumentException('Unrecognized element type.');
            }
        }

        $this->inlineElements = array_unique($this->inlineElements);
    }

    /**
     * addInlineElement
     *
     * @param string $element
     *
     * @return  static
     */
    public function addInlineElement(string $element): static
    {
        $this->inlineElements[] = strtolower(trim($element));

        return $this;
    }

    /**
     * addUnpairedElement
     *
     * @param string $element
     *
     * @return  static
     */
    public function addUnpairedElement(string $element): static
    {
        $this->unpairedElements[] = strtolower(trim($element));

        return $this;
    }

    /**
     * Method to set property unpairedElements
     *
     * @param array $unpairedElements
     *
     * @return  static  Return self to support chaining.
     */
    public function setUnpairedElements(array $unpairedElements): static
    {
        $this->unpairedElements = (array) $unpairedElements;

        return $this;
    }

    /**
     * Method to get property UnpairedElements
     *
     * @param bool $implode
     *
     * @return  array|string
     */
    public function getUnpairedElements(bool $implode = false): array|string
    {
        return $implode ? implode('|', $this->unpairedElements) : $this->unpairedElements;
    }

    /**
     * formatByDOMDocument
     *
     * @param string $string
     *
     * @return  string
     */
    public static function formatByDOMDocument(string $string): string
    {
        $dom = HTMLFactory::create();
        $dom->loadXML($string);
        $dom->formatOutput = true;

        return HTMLFactory::html5()->saveHTML($dom->documentElement);
    }
}
