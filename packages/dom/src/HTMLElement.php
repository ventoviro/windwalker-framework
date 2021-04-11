<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DOM;

/**
 * The HTMLElement class.
 */
abstract class HTMLElement
{
    /**
     * create
     *
     * @param  string  $name
     * @param  array   $attributes
     * @param  mixed   $content
     *
     * @return  DOMElement
     */
    public static function create(string $name, array $attributes = [], $content = null): DOMElement
    {
        return DOMElement::create($name, $attributes, $content)->asHTML();
    }

    /**
     * buildAttributes
     *
     * @param  array|DOMElement  $attributes
     *
     * @return  string
     */
    public static function buildAttributes(array|DOMElement $attributes): string
    {
        if ($attributes instanceof DOMElement) {
            $attributes = $attributes->getAttributes(true);
        }

        return DOMElement::buildAttributes($attributes, DOMElement::HTML);
    }
}
